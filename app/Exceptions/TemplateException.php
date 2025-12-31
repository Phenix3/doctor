<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception for template processing errors
 * 
 * Requirements: 8.5 - Template errors without system exposure
 */
class TemplateException extends DocumentGenerationException
{
    public function __construct(
        string $userMessage,
        string $templateType = '',
        string $country = '',
        string $language = '',
        ?Exception $previous = null
    ) {
        $context = array_filter([
            'template_type' => $templateType,
            'country' => $country,
            'language' => $language
        ]);

        parent::__construct(
            $userMessage,
            "Template processing failed for {$templateType} ({$country}, {$language})",
            $context,
            'TEMPLATE_ERROR',
            500,
            $previous
        );
    }
}

/**
 * Exception for missing templates
 */
class TemplateNotFoundException extends TemplateException
{
    public function __construct(
        string $templateType,
        string $country,
        string $language,
        ?Exception $previous = null
    ) {
        parent::__construct(
            "Le template demandé n'est pas disponible pour cette combinaison de pays et de langue.",
            $templateType,
            $country,
            $language,
            $previous
        );

        $this->errorCode = 'TEMPLATE_NOT_FOUND';
        $this->code = 404;
    }
}

/**
 * Exception for template rendering errors
 */
class TemplateRenderingException extends TemplateException
{
    public function __construct(
        string $templateType,
        string $country,
        string $language,
        string $renderingError = '',
        ?Exception $previous = null
    ) {
        parent::__construct(
            "Erreur lors du rendu du template. Veuillez vérifier vos données et réessayer.",
            $templateType,
            $country,
            $language,
            $previous
        );

        $this->context['rendering_error'] = $renderingError;
        $this->errorCode = 'TEMPLATE_RENDERING_ERROR';
    }
}