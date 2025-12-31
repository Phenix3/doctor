<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Exception for data integrity violations
 * 
 * Requirements: 1.2, 8.1, 8.3, 8.4 - Data integrity preservation
 */
class DataIntegrityException extends DocumentGenerationException
{
    protected array $integrityViolations;

    public function __construct(
        string $userMessage,
        array $integrityViolations = [],
        array $context = [],
        ?Exception $previous = null
    ) {
        $this->integrityViolations = $integrityViolations;

        parent::__construct(
            $userMessage,
            "Data integrity violation detected: " . count($integrityViolations) . " violations",
            array_merge($context, [
                'integrity_violations_count' => count($integrityViolations)
            ]),
            'DATA_INTEGRITY_VIOLATION',
            500,
            $previous
        );
    }

    /**
     * Get integrity violations
     */
    public function getIntegrityViolations(): array
    {
        return $this->integrityViolations;
    }

    /**
     * Render data integrity exception
     */
    public function render($request): JsonResponse
    {
        $this->logError();

        return response()->json([
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->userMessage,
                'timestamp' => now()->toISOString()
            ]
        ], 500);
    }
}

/**
 * Exception for user content modification violations
 */
class UserContentModificationException extends DataIntegrityException
{
    public function __construct(
        array $modifiedFields = [],
        ?Exception $previous = null
    ) {
        parent::__construct(
            "Les données fournies ont été altérées de manière inattendue. Veuillez réessayer.",
            $modifiedFields,
            ['modified_fields_count' => count($modifiedFields)],
            $previous
        );

        $this->errorCode = 'USER_CONTENT_MODIFIED';
    }
}

/**
 * Exception for session data corruption
 */
class SessionDataCorruptionException extends DataIntegrityException
{
    public function __construct(
        string $sessionId = '',
        string $corruptionType = '',
        ?Exception $previous = null
    ) {
        parent::__construct(
            "Erreur de session détectée. Veuillez recommencer la génération du document.",
            [],
            [
                'session_id_provided' => !empty($sessionId),
                'corruption_type' => $corruptionType
            ],
            $previous
        );

        $this->errorCode = 'SESSION_DATA_CORRUPTION';
    }
}