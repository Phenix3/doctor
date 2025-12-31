<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception for country adaptation errors
 * 
 * Requirements: 5.1-5.5, 8.5 - Country adaptation errors
 */
class CountryAdaptationException extends DocumentGenerationException
{
    public function __construct(
        string $userMessage,
        string $country = '',
        string $adaptationType = '',
        ?Exception $previous = null
    ) {
        $context = array_filter([
            'country' => $country,
            'adaptation_type' => $adaptationType
        ]);

        parent::__construct(
            $userMessage,
            "Country adaptation failed for {$country} ({$adaptationType})",
            $context,
            'COUNTRY_ADAPTATION_ERROR',
            500,
            $previous
        );
    }
}

/**
 * Exception for unsupported countries
 */
class UnsupportedCountryException extends CountryAdaptationException
{
    public function __construct(string $country, ?Exception $previous = null)
    {
        parent::__construct(
            "Le pays sélectionné n'est pas encore supporté par le système.",
            $country,
            'unsupported_country',
            $previous
        );

        $this->errorCode = 'UNSUPPORTED_COUNTRY';
        $this->code = 400;
    }
}

/**
 * Exception for missing country rules
 */
class CountryRulesNotFoundException extends CountryAdaptationException
{
    public function __construct(string $country, ?Exception $previous = null)
    {
        parent::__construct(
            "Les règles d'adaptation pour ce pays ne sont pas disponibles.",
            $country,
            'missing_rules',
            $previous
        );

        $this->errorCode = 'COUNTRY_RULES_NOT_FOUND';
        $this->code = 404;
    }
}

/**
 * Exception for country adaptation conflicts
 */
class CountryAdaptationConflictException extends CountryAdaptationException
{
    public function __construct(
        string $country,
        string $conflictDescription = '',
        ?Exception $previous = null
    ) {
        parent::__construct(
            "Conflit détecté lors de l'adaptation pour ce pays. Veuillez contacter le support.",
            $country,
            'adaptation_conflict',
            $previous
        );

        $this->context['conflict_description'] = $conflictDescription;
        $this->errorCode = 'COUNTRY_ADAPTATION_CONFLICT';
    }
}