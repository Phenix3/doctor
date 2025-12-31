<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\BooleanType;
use Spatie\LaravelData\Attributes\Validation\ArrayType;

class DocumentGenerationResult extends Data
{
    public function __construct(
        #[Required, BooleanType]
        public bool $success,
        
        #[StringType]
        public ?string $content = null,
        
        #[StringType]
        public ?string $session_id = null,
        
        #[StringType]
        public ?string $document_id = null,
        
        #[ArrayType]
        public array $metadata = [],
        
        #[ArrayType]
        public array $errors = [],
        
        #[StringType]
        public ?string $error_message = null,
    ) {}

    /**
     * Create successful result
     */
    public static function success(
        string $content,
        string $sessionId,
        ?string $documentId = null,
        array $metadata = []
    ): self {
        return new self(
            success: true,
            content: $content,
            session_id: $sessionId,
            document_id: $documentId,
            metadata: $metadata
        );
    }

    /**
     * Create failed result
     */
    public static function failure(
        string $errorMessage,
        array $errors = [],
        ?string $sessionId = null
    ): self {
        return new self(
            success: false,
            session_id: $sessionId,
            errors: $errors,
            error_message: $errorMessage
        );
    }

    /**
     * Check if generation was successful
     */
    public function isSuccessful(): bool
    {
        return $this->success && !empty($this->content);
    }

    /**
     * Check if generation failed
     */
    public function hasFailed(): bool
    {
        return !$this->success || !empty($this->errors);
    }

    /**
     * Get formatted error message
     */
    public function getFormattedErrorMessage(): string
    {
        if (!empty($this->error_message)) {
            return $this->error_message;
        }

        if (!empty($this->errors)) {
            return 'Erreurs de validation: ' . implode(', ', $this->errors);
        }

        return 'Une erreur inconnue s\'est produite lors de la génération du document.';
    }

    /**
     * Add metadata
     */
    public function withMetadata(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);
        return $this;
    }

    /**
     * Get generation statistics
     */
    public function getGenerationStats(): array
    {
        return [
            'success' => $this->success,
            'content_length' => $this->content ? strlen($this->content) : 0,
            'has_errors' => !empty($this->errors),
            'error_count' => count($this->errors),
            'metadata_count' => count($this->metadata),
        ];
    }
}