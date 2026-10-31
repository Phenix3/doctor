<?php

namespace App\Data;

use App\Enums\Country;
use App\Enums\DocumentType;
use App\Enums\ToneLevel;
use Spatie\LaravelData\Attributes\Validation\ArrayType;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class DocumentGenerationRequest extends Data
{
    public function __construct(
        #[Required, StringType, In(['SENEGAL', 'COTE_DIVOIRE', 'BURKINA_FASO', 'MALI', 'NIGER', 'GUINEA', 'BENIN', 'TOGO', 'MAURITANIA', 'CAMEROON', 'CHAD', 'CENTRAL_AFRICAN_REPUBLIC', 'GABON', 'EQUATORIAL_GUINEA', 'CONGO', 'DEMOCRATIC_REPUBLIC_OF_CONGO', 'MADAGASCAR', 'COMOROS', 'DJIBOUTI', 'BURUNDI', 'RWANDA'])]
        public string $country,

        #[Required, StringType, In(['ADMINISTRATIVE_APPLICATION', 'MOTIVATION_LETTER', 'INTERNSHIP_REQUEST', 'HONOR_ATTESTATION', 'COMPLAINT_LETTER', 'ADMINISTRATIVE_ACT_REQUEST', 'APPEAL_OR_POSTPONEMENT_LETTER', 'GENERIC_ADMINISTRATIVE_CORRESPONDENCE'])]
        public string $document_type,

        #[Required, StringType, In(['SIMPLE', 'OFFICIAL', 'VERY_OFFICIAL'])]
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
        $validated = $request->validated();

        return new self(
            country: $validated['country'],
            document_type: $validated['document_type'],
            tone_level: $validated['tone_level'],
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
        return Country::make($this->country);
    }

    /**
     * Get country value (for country adapter)
     */
    public function getCountryValue(): string
    {
        return $this->getCountryEnum()->value;
    }

    /**
     * Get DocumentType enum instance
     */
    public function getDocumentTypeEnum(): DocumentType
    {
        return DocumentType::make($this->document_type);
    }

    /**
     * Get document type value (for template engine)
     */
    public function getDocumentTypeValue(): string
    {
        return $this->getDocumentTypeEnum()->value;
    }

    /**
     * Get ToneLevel enum instance
     */
    public function getToneLevelEnum(): ToneLevel
    {
        return ToneLevel::make($this->tone_level);
    }

    /**
     * Get tone level value (for country adapter)
     */
    public function getToneLevelValue(): string
    {
        return $this->getToneLevelEnum()->value;
    }

    /**
     * Validate that all required fields for document type are present
     */
    public function hasAllRequiredFields(): bool
    {
        $requiredFields = \App\Http\Requests\DocumentRequest::getRequiredFieldsForDocumentType(
            $this->document_type
        );

        foreach ($requiredFields as $field) {
            if (! isset($this->fields[$field]) || empty($this->fields[$field])) {
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
            $this->document_type
        );

        $missing = [];
        foreach ($requiredFields as $field) {
            if (! isset($this->fields[$field]) || empty($this->fields[$field])) {
                $missing[] = $field;
            }
        }

        return $missing;
    }
}
