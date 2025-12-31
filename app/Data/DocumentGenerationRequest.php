<?php

namespace App\Data;

use App\Enums\Country;
use App\Enums\DocumentType;
use App\Enums\ToneLevel;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\ArrayType;

class DocumentGenerationRequest extends Data
{
    public function __construct(
        #[Required, StringType, In(['SN', 'CI', 'BF', 'ML', 'NE', 'GN', 'BJ', 'TG', 'MR', 'CM', 'TD', 'CF', 'GA', 'GQ', 'CG', 'CD', 'MG', 'KM', 'DJ', 'BI', 'RW'])]
        public string $country,
        
        #[Required, StringType, In(['administrative_application', 'motivation_letter', 'internship_request', 'honor_attestation', 'complaint_letter', 'administrative_act_request', 'appeal_or_postponement_letter', 'generic_administrative_correspondence'])]
        public string $document_type,
        
        #[Required, StringType, In(['simple', 'official', 'very_official'])]
        public string $tone_level,
        
        #[Required, StringType, In(['fr', 'en'])]
        public string $language,
        
        #[Required, ArrayType]
        public array $fields,
        
        public ?string $session_id = null,
    ) {}

    /**
     * Create from Laravel Form Request
     */
    public static function fromRequest(\App\Http\Requests\DocumentRequest $request): self
    {
        $validated = $request->getValidatedData();
        
        return new self(
            country: $validated['country']->value,
            document_type: $validated['document_type']->value,
            tone_level: $validated['tone_level']->value,
            language: $validated['language'],
            fields: $validated['fields'],
            session_id: session()->getId()
        );
    }

    /**
     * Get Country enum instance
     */
    public function getCountryEnum(): Country
    {
        return Country::from($this->country);
    }

    /**
     * Get DocumentType enum instance
     */
    public function getDocumentTypeEnum(): DocumentType
    {
        return DocumentType::from($this->document_type);
    }

    /**
     * Get ToneLevel enum instance
     */
    public function getToneLevelEnum(): ToneLevel
    {
        return ToneLevel::from($this->tone_level);
    }

    /**
     * Validate that all required fields for document type are present
     */
    public function hasAllRequiredFields(): bool
    {
        $requiredFields = \App\Http\Requests\DocumentRequest::getRequiredFieldsForDocumentType(
            strtoupper($this->document_type)
        );
        
        foreach ($requiredFields as $field) {
            if (!isset($this->fields[$field]) || empty($this->fields[$field])) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get missing required fields
     */
    public function getMissingRequiredFields(): array
    {
        $requiredFields = \App\Http\Requests\DocumentRequest::getRequiredFieldsForDocumentType(
            strtoupper($this->document_type)
        );
        
        $missing = [];
        foreach ($requiredFields as $field) {
            if (!isset($this->fields[$field]) || empty($this->fields[$field])) {
                $missing[] = $field;
            }
        }
        
        return $missing;
    }
}