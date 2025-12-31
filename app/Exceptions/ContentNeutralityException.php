<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Exception for content neutrality violations
 * 
 * Requirements: 6.1-6.5, 8.5 - Content neutrality enforcement
 */
class ContentNeutralityException extends DocumentGenerationException
{
    protected array $violations;

    public function __construct(
        string $userMessage,
        array $violations = [],
        array $context = [],
        ?Exception $previous = null
    ) {
        $this->violations = $violations;

        parent::__construct(
            $userMessage,
            "Content neutrality violations detected: " . count($violations) . " violations",
            array_merge($context, [
                'violation_count' => count($violations),
                'violation_types' => array_keys($violations)
            ]),
            'CONTENT_NEUTRALITY_VIOLATION',
            400,
            $previous
        );
    }

    /**
     * Get neutrality violations
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * Render content neutrality exception with violation details
     */
    public function render($request): JsonResponse
    {
        $this->logError();

        return response()->json([
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->userMessage,
                'violations' => $this->formatViolationsForUser(),
                'timestamp' => now()->toISOString()
            ]
        ], 400);
    }

    /**
     * Format violations for user display (without exposing patterns)
     */
    protected function formatViolationsForUser(): array
    {
        $userFriendlyViolations = [];

        foreach ($this->violations as $type => $violationList) {
            $userFriendlyViolations[] = [
                'type' => $type,
                'count' => count($violationList),
                'description' => $this->getViolationDescription($type)
            ];
        }

        return $userFriendlyViolations;
    }

    /**
     * Get user-friendly violation descriptions
     */
    protected function getViolationDescription(string $type): string
    {
        $descriptions = [
            'legal_advice' => 'Le contenu contient des conseils juridiques non autorisés.',
            'opinions' => 'Le contenu contient des opinions ou jugements subjectifs.',
            'personal_language' => 'Le contenu utilise un langage personnel non autorisé.',
            'legal_references' => 'Le contenu contient des références légales non autorisées.',
            'content_modification' => 'Le contenu a été modifié au-delà du formatage autorisé.',
            'user_content_modification' => 'Le contenu utilisateur a été modifié de manière non autorisée.'
        ];

        return $descriptions[$type] ?? 'Violation de neutralité détectée.';
    }
}