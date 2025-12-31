<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Exception for output formatting errors
 * 
 * Requirements: 1.5, 7.1, 7.2, 7.3, 8.5 - Output formatting errors
 */
class OutputFormattingException extends DocumentGenerationException
{
    public function __construct(
        string $userMessage,
        string $formatType = '',
        array $formatOptions = [],
        ?Exception $previous = null
    ) {
        $context = array_filter([
            'format_type' => $formatType,
            'format_options_count' => count($formatOptions)
        ]);

        parent::__construct(
            $userMessage,
            "Output formatting failed for {$formatType}",
            $context,
            'OUTPUT_FORMATTING_ERROR',
            500,
            $previous
        );
    }
}

/**
 * Exception for PDF generation errors
 */
class PDFGenerationException extends OutputFormattingException
{
    public function __construct(
        string $pdfError = '',
        array $pdfOptions = [],
        ?Exception $previous = null
    ) {
        parent::__construct(
            "Erreur lors de la génération du PDF. Veuillez réessayer.",
            'PDF',
            $pdfOptions,
            $previous
        );

        $this->context['pdf_error'] = $pdfError;
        $this->errorCode = 'PDF_GENERATION_ERROR';
    }
}

/**
 * Exception for invalid PDF options
 */
class InvalidPDFOptionsException extends OutputFormattingException
{
    protected array $validationErrors;

    public function __construct(
        array $validationErrors,
        array $pdfOptions = [],
        ?Exception $previous = null
    ) {
        $this->validationErrors = $validationErrors;

        parent::__construct(
            "Les options PDF fournies sont invalides.",
            'PDF_OPTIONS',
            $pdfOptions,
            $previous
        );

        $this->context['validation_errors'] = $validationErrors;
        $this->errorCode = 'INVALID_PDF_OPTIONS';
        $this->code = 400;
    }

    /**
     * Get PDF validation errors
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Render with validation errors
     */
    public function render($request): JsonResponse
    {
        $this->logError();

        return response()->json([
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->userMessage,
                'validation_errors' => $this->validationErrors,
                'timestamp' => now()->toISOString()
            ]
        ], 400);
    }
}

/**
 * Exception for content sanitization errors
 */
class ContentSanitizationException extends OutputFormattingException
{
    public function __construct(
        string $sanitizationError = '',
        ?Exception $previous = null
    ) {
        parent::__construct(
            "Erreur lors de la préparation du contenu pour l'export.",
            'SANITIZATION',
            [],
            $previous
        );

        $this->context['sanitization_error'] = $sanitizationError;
        $this->errorCode = 'CONTENT_SANITIZATION_ERROR';
    }
}