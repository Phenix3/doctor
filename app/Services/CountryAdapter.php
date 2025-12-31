<?php

namespace App\Services;

use App\Models\CountryRule;
use App\Enums\Country;
use App\Enums\ToneLevel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CountryAdapter
{
    private const CACHE_TTL = 3600; // 1 hour cache
    private const CACHE_PREFIX = 'country_rules:';

    /**
     * Adapt content for specific country
     */
    public function adaptForCountry(string $content, string $countryCode): string
    {
        $countryRule = $this->getCountryRule($countryCode);
        
        if (!$countryRule) {
            Log::warning("Country rule not found for country: {$countryCode}");
            return $content;
        }

        // Apply country-specific terminology
        $content = $this->applyAdministrativeTerminology($content, $countryRule);
        
        return $content;
    }

    /**
     * Apply politeness formulas for country and tone level
     */
    public function applyPolitenesFormulas(string $content, string $countryCode, string $toneLevel): string
    {
        $countryRule = $this->getCountryRule($countryCode);
        
        if (!$countryRule) {
            Log::warning("Country rule not found for country: {$countryCode}");
            return $content;
        }

        $openingFormula = $countryRule->getOpeningFormula($toneLevel);
        $closingFormula = $countryRule->getClosingFormula($toneLevel);

        // Replace placeholders in content
        if ($openingFormula) {
            $content = str_replace('{{opening_formula}}', $openingFormula, $content);
        }
        
        if ($closingFormula) {
            $content = str_replace('{{closing_formula}}', $closingFormula, $content);
        }

        return $content;
    }

    /**
     * Get formatting rules for a country
     */
    public function getFormattingRules(string $countryCode): ?CountryRule
    {
        return $this->getCountryRule($countryCode);
    }

    /**
     * Format date according to country rules
     */
    public function formatDate(\DateTime $date, string $countryCode): string
    {
        $countryRule = $this->getCountryRule($countryCode);
        
        if (!$countryRule) {
            return $date->format('d/m/Y'); // Default format
        }

        return $countryRule->formatDate($date);
    }

    /**
     * Format address according to country rules
     */
    public function formatAddress(array $addressParts, string $countryCode): string
    {
        $countryRule = $this->getCountryRule($countryCode);
        
        if (!$countryRule) {
            // Default address format
            return implode(', ', array_filter($addressParts));
        }

        return $countryRule->formatAddress($addressParts);
    }

    /**
     * Get administrative term for country
     */
    public function getAdministrativeTerm(string $term, string $countryCode): ?string
    {
        $countryRule = $this->getCountryRule($countryCode);
        
        if (!$countryRule) {
            return null;
        }

        return $countryRule->getAdministrativeTerm($term);
    }

    /**
     * Get document structure rules for country
     */
    public function getDocumentStructureRules(string $countryCode): array
    {
        $countryRule = $this->getCountryRule($countryCode);
        
        if (!$countryRule) {
            return [
                'header_required' => true,
                'date_position' => 'top_right',
                'signature_position' => 'bottom_right',
            ];
        }

        return $countryRule->document_structure_rules ?? [];
    }

    /**
     * Get all supported countries with their rules
     */
    public function getSupportedCountries(): array
    {
        return Cache::remember(
            self::CACHE_PREFIX . 'supported_countries',
            self::CACHE_TTL,
            function () {
                return CountryRule::active()
                    ->select('country_code', 'country_name', 'default_language')
                    ->get()
                    ->keyBy('country_code')
                    ->toArray();
            }
        );
    }

    /**
     * Check if country is supported
     */
    public function isCountrySupported(string $countryCode): bool
    {
        $supportedCountries = $this->getSupportedCountries();
        return isset($supportedCountries[$countryCode]);
    }

    /**
     * Get country rule with caching
     */
    private function getCountryRule(string $countryCode): ?CountryRule
    {
        return Cache::remember(
            self::CACHE_PREFIX . $countryCode,
            self::CACHE_TTL,
            function () use ($countryCode) {
                return CountryRule::getByCountry($countryCode);
            }
        );
    }

    /**
     * Apply administrative terminology replacements
     */
    private function applyAdministrativeTerminology(string $content, CountryRule $countryRule): string
    {
        $terminology = $countryRule->administrative_terminology ?? [];
        
        foreach ($terminology as $generic => $specific) {
            // Replace generic terms with country-specific ones
            $content = str_ireplace("{{term:{$generic}}}", $specific, $content);
        }

        return $content;
    }

    /**
     * Clear country rules cache
     */
    public function clearCache(?string $countryCode = null): void
    {
        if ($countryCode) {
            Cache::forget(self::CACHE_PREFIX . $countryCode);
        } else {
            // Clear all country-related cache
            Cache::forget(self::CACHE_PREFIX . 'supported_countries');
            
            $supportedCountries = CountryRule::active()->pluck('country_code');
            foreach ($supportedCountries as $code) {
                Cache::forget(self::CACHE_PREFIX . $code);
            }
        }
    }

    /**
     * Validate country adaptation integrity
     */
    public function validateAdaptation(string $originalContent, string $adaptedContent, string $countryCode): bool
    {
        // Ensure core meaning is preserved
        $originalWords = str_word_count($originalContent);
        $adaptedWords = str_word_count($adaptedContent);
        
        // Allow up to 50% variation in word count (for politeness formulas and terminology)
        $variation = abs($adaptedWords - $originalWords) / max($originalWords, 1);
        
        if ($variation > 0.5) {
            Log::warning("Significant content variation detected during country adaptation", [
                'country_code' => $countryCode,
                'original_words' => $originalWords,
                'adapted_words' => $adaptedWords,
                'variation' => $variation
            ]);
            return false;
        }

        return true;
    }
}