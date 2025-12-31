<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Base exception for document generation errors
 * 
 * Requirements: 8.5 - Clear error messages without system exposure
 */
class DocumentGenerationException extends Exception
{
    protected string $userMessage;
    protected array $context;
    protected string $errorCode;

    public function __construct(
        string $userMessage,
        string $internalMessage = '',
        array $context = [],
        string $errorCode = 'DOCUMENT_GENERATION_ERROR',
        int $code = 500,
        ?Exception $previous = null
    ) {
        $this->userMessage = $userMessage;
        $this->context = $context;
        $this->errorCode = $errorCode;

        // Use internal message for logging, user message for display
        parent::__construct($internalMessage ?: $userMessage, $code, $previous);
    }

    /**
     * Get user-friendly error message
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * Get error context for logging
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get error code for identification
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Render the exception as an HTTP response
     */
    public function render(Request $request): JsonResponse
    {
        // Log the error with context (without user data)
        $this->logError();

        return response()->json([
            'success' => false,
            'error' => [
                'code' => $this->errorCode,
                'message' => $this->userMessage,
                'timestamp' => now()->toISOString()
            ]
        ], $this->code >= 400 && $this->code < 600 ? $this->code : 500);
    }

    /**
     * Log error with appropriate level and context
     * Requirements: 8.5 - Logging without user data exposure
     */
    protected function logError(): void
    {
        $logContext = array_merge($this->context, [
            'error_code' => $this->errorCode,
            'exception_class' => static::class,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'http_code' => $this->code,
            'trace_hash' => md5($this->getTraceAsString()),
            'timestamp' => now()->toISOString()
        ]);

        // Add request context if available
        if (app()->bound('request')) {
            $request = request();
            $logContext['request'] = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'user_id' => auth()->check() ? auth()->id() : null
            ];
        }

        // Add previous exception context
        if ($this->getPrevious()) {
            $logContext['previous_exception'] = [
                'class' => get_class($this->getPrevious()),
                'message' => $this->getPrevious()->getMessage(),
                'file' => $this->getPrevious()->getFile(),
                'line' => $this->getPrevious()->getLine()
            ];
        }

        // Remove any potential user data from logging
        $logContext = $this->sanitizeLogContext($logContext);

        // Determine log level based on error severity
        $level = $this->getLogLevel();
        
        match ($level) {
            'debug' => Log::debug($this->getMessage(), $logContext),
            'info' => Log::info($this->getMessage(), $logContext),
            'notice' => Log::notice($this->getMessage(), $logContext),
            'warning' => Log::warning($this->getMessage(), $logContext),
            'error' => Log::error($this->getMessage(), $logContext),
            'critical' => Log::critical($this->getMessage(), $logContext),
            'alert' => Log::alert($this->getMessage(), $logContext),
            'emergency' => Log::emergency($this->getMessage(), $logContext),
            default => Log::error($this->getMessage(), $logContext)
        };
    }

    /**
     * Get appropriate log level based on exception type and code
     */
    protected function getLogLevel(): string
    {
        // Critical system errors
        if ($this->code >= 500 && $this->code < 600) {
            if (str_contains($this->errorCode, 'DATA_INTEGRITY') ||
                str_contains($this->errorCode, 'SESSION_CORRUPTION') ||
                str_contains($this->errorCode, 'UNEXPECTED_ERROR')) {
                return 'critical';
            }
            return 'error';
        }

        // Client errors
        if ($this->code >= 400 && $this->code < 500) {
            if (str_contains($this->errorCode, 'VALIDATION') ||
                str_contains($this->errorCode, 'CONTENT_NEUTRALITY')) {
                return 'info';
            }
            return 'warning';
        }

        return 'error';
    }

    /**
     * Sanitize log context to remove user data
     * Requirements: 8.5 - No user data in logs
     */
    protected function sanitizeLogContext(array $context): array
    {
        $sensitiveKeys = [
            // User data fields
            'fields', 'user_data', 'personal_info', 'content',
            'applicant_name', 'student_name', 'declarant_name',
            'complainant_name', 'requester_name', 'appellant_name',
            'sender_name', 'address', 'phone', 'email',
            
            // Authentication and security
            'password', 'token', 'api_key', '_token', 'csrf_token',
            'access_token', 'refresh_token', 'session_token',
            
            // Document content
            'document_content', 'generated_content', 'template_content',
            'user_input', 'form_data'
        ];

        foreach ($sensitiveKeys as $key) {
            if (isset($context[$key])) {
                if (is_string($context[$key])) {
                    $context[$key] = '[REDACTED]';
                } elseif (is_array($context[$key])) {
                    $context[$key] = [
                        'type' => 'array',
                        'count' => count($context[$key]), 
                        'data' => '[REDACTED]'
                    ];
                } elseif (is_object($context[$key])) {
                    $context[$key] = [
                        'type' => 'object',
                        'class' => get_class($context[$key]),
                        'data' => '[REDACTED]'
                    ];
                } else {
                    $context[$key] = '[REDACTED]';
                }
            }
        }

        // Recursively sanitize nested arrays
        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $context[$key] = $this->sanitizeLogContext($value);
            } elseif (is_object($value)) {
                // Convert objects to safe representation
                if (method_exists($value, 'toArray')) {
                    $context[$key] = [
                        'type' => 'object',
                        'class' => get_class($value),
                        'data' => $this->sanitizeLogContext($value->toArray())
                    ];
                } else {
                    $context[$key] = [
                        'type' => 'object',
                        'class' => get_class($value),
                        'data' => '[OBJECT_NOT_SERIALIZABLE]'
                    ];
                }
            }
        }

        // Sanitize specific patterns in string values
        foreach ($context as $key => $value) {
            if (is_string($value)) {
                // Remove potential email addresses
                $value = preg_replace('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', '[EMAIL_REDACTED]', $value);
                
                // Remove potential phone numbers
                $value = preg_replace('/(\+?[0-9]{1,4}[-.\s]?)?(\(?[0-9]{1,4}\)?[-.\s]?)?[0-9]{3,4}[-.\s]?[0-9]{3,4}/', '[PHONE_REDACTED]', $value);
                
                $context[$key] = $value;
            }
        }

        return $context;
    }
}