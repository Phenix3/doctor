<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Exception for input validation errors
 * 
 * Requirements: 2.1, 2.2, 2.4 - Clear validation error messages
 */
class ValidationException extends DocumentGenerationException
{
    protected array $validationErrors;

    public function __construct(
        string $userMessage,
        array $validationErrors = [],
        array $context = [],
        ?Exception $previous = null
    ) {
        $this->validationErrors = $validationErrors;

        parent::__construct(
            $userMessage,
            "Validation failed: " . implode(', ', $validationErrors),
            array_merge($context, ['validation_errors_count' => count($validationErrors)]),
            'VALIDATION_ERROR',
            422,
            $previous
        );
    }

    /**
     * Get validation errors
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Render validation exception with detailed errors
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
        ], 422);
    }
}