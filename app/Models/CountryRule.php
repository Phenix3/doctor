<?php

namespace App\Models;

use App\Enums\Country;
use App\Enums\ToneLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CountryRule extends Model
{
    use HasFactory;
    protected $fillable = [
        'country_code',
        'country_name',
        'politeness_formulas',
        'date_format',
        'address_format',
        'administrative_terminology',
        'document_structure_rules',
        'default_language',
        'is_active',
    ];

    protected $casts = [
        'politeness_formulas' => 'array',
        'address_format' => 'array',
        'administrative_terminology' => 'array',
        'document_structure_rules' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Scope to get active country rules only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by country code
     */
    public function scopeForCountry(Builder $query, string $countryCode): Builder
    {
        return $query->where('country_code', $countryCode);
    }

    /**
     * Get country rule by country code
     */
    public static function getByCountry(string $countryCode): ?self
    {
        return static::active()
            ->forCountry($countryCode)
            ->first();
    }

    /**
     * Get politeness formula for specific tone level
     */
    public function getPolitenesFormula(string $toneLevel): ?string
    {
        $formulas = $this->politeness_formulas ?? [];
        
        return $formulas[$toneLevel] ?? $formulas['official'] ?? null;
    }

    /**
     * Get opening formula for documents
     */
    public function getOpeningFormula(string $toneLevel): ?string
    {
        $formulas = $this->politeness_formulas ?? [];
        $toneLevelFormulas = $formulas[$toneLevel] ?? $formulas['official'] ?? [];
        
        return $toneLevelFormulas['opening'] ?? null;
    }

    /**
     * Get closing formula for documents
     */
    public function getClosingFormula(string $toneLevel): ?string
    {
        $formulas = $this->politeness_formulas ?? [];
        $toneLevelFormulas = $formulas[$toneLevel] ?? $formulas['official'] ?? [];
        
        return $toneLevelFormulas['closing'] ?? null;
    }

    /**
     * Get administrative term translation
     */
    public function getAdministrativeTerm(string $term): ?string
    {
        $terminology = $this->administrative_terminology ?? [];
        
        return $terminology[$term] ?? null;
    }

    /**
     * Format date according to country rules
     */
    public function formatDate(\DateTime $date): string
    {
        return $date->format($this->date_format);
    }

    /**
     * Format address according to country rules
     */
    public function formatAddress(array $addressParts): string
    {
        $format = $this->address_format ?? [];
        $template = $format['template'] ?? '{street}, {city}, {country}';
        
        foreach ($addressParts as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        return $template;
    }
}
