<?php

namespace App\Models;

use App\Enums\Country;
use App\Enums\DocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DocumentTemplate extends Model
{
    use HasFactory;
    protected $fillable = [
        'document_type',
        'country',
        'language',
        'template_content',
        'required_fields',
        'optional_fields',
        'validation_rules',
        'is_active',
    ];

    protected $casts = [
        'required_fields' => 'array',
        'optional_fields' => 'array',
        'validation_rules' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship to generated documents
     */
    public function generatedDocuments(): HasMany
    {
        return $this->hasMany(GeneratedDocument::class, 'document_type', 'document_type');
    }

    /**
     * Scope to get active templates only
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by document type
     */
    public function scopeForDocumentType(Builder $query, string $documentType): Builder
    {
        return $query->where('document_type', $documentType);
    }

    /**
     * Scope to filter by country
     */
    public function scopeForCountry(Builder $query, string $country): Builder
    {
        return $query->where('country', $country);
    }

    /**
     * Scope to filter by language
     */
    public function scopeForLanguage(Builder $query, string $language): Builder
    {
        return $query->where('language', $language);
    }

    /**
     * Get template for specific combination
     */
    public static function getTemplate(string $documentType, string $country, string $language = 'fr'): ?self
    {
        return static::active()
            ->forDocumentType($documentType)
            ->forCountry($country)
            ->forLanguage($language)
            ->first();
    }

    /**
     * Check if all required fields are provided
     */
    public function hasAllRequiredFields(array $providedFields): bool
    {
        $requiredFields = $this->required_fields ?? [];
        
        foreach ($requiredFields as $field) {
            if (!isset($providedFields[$field]) || empty($providedFields[$field])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get missing required fields
     */
    public function getMissingRequiredFields(array $providedFields): array
    {
        $requiredFields = $this->required_fields ?? [];
        $missing = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($providedFields[$field]) || empty($providedFields[$field])) {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }
}
