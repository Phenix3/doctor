<?php

namespace App\Rules;

use App\Services\ContentNeutralityValidator;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Custom validation rule for content neutrality
 * 
 * Validates that user input doesn't contain prohibited content that would
 * violate neutrality requirements (6.1-6.5)
 */
class ContentNeutralityRule implements ValidationRule
{
    private ContentNeutralityValidator $validator;
    private string $language;

    public function __construct(string $language = 'fr')
    {
        $this->validator = app(ContentNeutralityValidator::class);
        $this->language = $language;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || empty($value)) {
            return;
        }

        // Validate content neutrality
        $result = $this->validator->validateContentNeutrality($value);

        if (!$result['is_neutral']) {
            $violationMessages = $this->validator->getViolationMessages(
                $result['violations'],
                $this->language
            );

            // Create a user-friendly error message
            $errorMessage = $this->language === 'fr' 
                ? 'Le contenu fourni contient des éléments non autorisés (conseils juridiques, opinions, références légales).'
                : 'The provided content contains unauthorized elements (legal advice, opinions, legal references).';

            $fail($errorMessage);
        }
    }
}