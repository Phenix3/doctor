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
     * Get all politeness formulas for a tone level
     */
    public function getAllFormulasForTone(string $toneLevel): array
    {
        $formulas = $this->politeness_formulas ?? [];
        return $formulas[$toneLevel] ?? $formulas['official'] ?? [];
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
     * Get all administrative terms
     */
    public function getAllAdministrativeTerms(): array
    {
        return $this->administrative_terminology ?? [];
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

    /**
     * Get document structure rule
     */
    public function getDocumentStructureRule(string $rule): mixed
    {
        $rules = $this->document_structure_rules ?? [];
        return $rules[$rule] ?? null;
    }

    /**
     * Check if reference is required for documents
     */
    public function isReferenceRequired(): bool
    {
        return $this->getDocumentStructureRule('reference_required') ?? false;
    }

    /**
     * Get date position in document
     */
    public function getDatePosition(): string
    {
        return $this->getDocumentStructureRule('date_position') ?? 'top_right';
    }

    /**
     * Get signature position in document
     */
    public function getSignaturePosition(): string
    {
        return $this->getDocumentStructureRule('signature_position') ?? 'bottom_right';
    }
}
