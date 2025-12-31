<?php

namespace App\Services;

use App\Exceptions\DocumentGenerationException;
use App\Exceptions\ValidationException;
use App\Exceptions\TemplateException;
use App\Exceptions\TemplateNotFoundException;
use App\Exceptions\TemplateRenderingException;
use App\Exceptions\CountryAdaptationException;
use App\Exceptions\UnsupportedCountryException;
use App\Exceptions\CountryRulesNotFoundException;
use App\Exceptions\CountryAdaptationConflictException;
use App\Exceptions\ContentNeutralityException;
use App\Exceptions\OutputFormattingException;
use App\Exceptions\PDFGenerationException;
use App\Exceptions\InvalidPDFOptionsException;
use App\Exceptions\ContentSanitizationException;
use App\Exceptions\DataIntegrityException;
use App\Exceptions\UserContentModificationException;
use App\Exceptions\SessionDataCorruptionException;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Service for consistent error handling across the application
 * 
 * Requirements: 8.5 - Clear error messages without system exposure
 */
class ErrorHandlingService
{
    /**
     * Handle validation errors
     */
    public function handleValidationError(
        string $userMessage,
        array $validationErrors = [],
        array $context = []
    ): ValidationException {
        return new ValidationException($userMessage, $validationErrors, $context);
    }

    /**
     * Handle template errors
     */
    public function handleTemplateError(
        string $userMessage,
        string $templateType = '',
        string $country = '',
        string $language = '',
        Exception $previous = null
    ): TemplateException {
        return new TemplateException($userMessage, $templateType, $country, $language, $previous);
    }

    /**
     * Handle template not found errors
     */
    public function handleTemplateNotFound(
        string $templateType,
        string $country,
        string $language
    ): TemplateNotFoundException {
        return new TemplateNotFoundException($templateType, $country, $language);
    }

    /**
     * Handle template rendering errors
     */
    public function handleTemplateRenderingError(
        string $templateType,
        string $country,
        string $language,
        string $renderingError = '',
        Exception $previous = null
    ): TemplateRenderingException {
        return new TemplateRenderingException(
            $templateType, 
            $country, 
            $language, 
            $renderingError, 
            $previous
        );
    }

    /**
     * Handle unsupported country errors
     */
    public function handleUnsupportedCountry(string $country): UnsupportedCountryException
    {
        return new UnsupportedCountryException($country);
    }

    /**
     * Handle missing country rules errors
     */
    public function handleCountryRulesNotFound(string $country): CountryRulesNotFoundException
    {
        return new CountryRulesNotFoundException($country);
    }

    /**
     * Handle country adaptation conflicts
     */
    public function handleCountryAdaptationConflict(
        string $country,
        string $conflictDescription = ''
    ): CountryAdaptationConflictException {
        return new CountryAdaptationConflictException($country, $conflictDescription);
    }

    /**
     * Handle content neutrality violations
     */
    public function handleContentNeutralityViolation(
        string $userMessage,
        array $violations = [],
        array $context = []
    ): ContentNeutralityException {
        return new ContentNeutralityException($userMessage, $violations, $context);
    }

    /**
     * Handle PDF generation errors
     */
    public function handlePDFGenerationError(
        string $pdfError = '',
        array $pdfOptions = []
    ): PDFGenerationException {
        return new PDFGenerationException($pdfError, $pdfOptions);
    }

    /**
     * Handle invalid PDF options
     */
    public function handleInvalidPDFOptions(
        array $validationErrors,
        array $pdfOptions = []
    ): InvalidPDFOptionsException {
        return new InvalidPDFOptionsException($validationErrors, $pdfOptions);
    }

    /**
     * Handle content sanitization errors
     */
    public function handleContentSanitizationError(
        string $sanitizationError = ''
    ): ContentSanitizationException {
        return new ContentSanitizationException($sanitizationError);
    }

    /**
     * Handle user content modification violations
     */
    public function handleUserContentModification(
        array $modifiedFields = []
    ): UserContentModificationException {
        return new UserContentModificationException($modifiedFields);
    }

    /**
     * Handle session data corruption
     */
    public function handleSessionDataCorruption(
        string $sessionId = '',
        string $corruptionType = ''
    ): SessionDataCorruptionException {
        return new SessionDataCorruptionException($sessionId, $corruptionType);
    }

    /**
     * Handle generic document generation errors
     */
    public function handleDocumentGenerationError(
        string $userMessage,
        string $internalMessage = '',
        array $context = [],
        string $errorCode = 'DOCUMENT_GENERATION_ERROR',
        int $httpCode = 500
    ): DocumentGenerationException {
        return new DocumentGenerationException(
            $userMessage,
            $internalMessage,
            $context,
            $errorCode,
            $httpCode
        );
    }

    /**
     * Wrap and handle unexpected exceptions
     */
    public function wrapUnexpectedException(
        Exception $exception,
        string $userMessage = "Une erreur inattendue s'est produite. Veuillez réessayer.",
        array $context = []
    ): DocumentGenerationException {
        // Log the original exception with full details
        Log::error('Unexpected exception wrapped', [
            'original_exception' => get_class($exception),
            'original_message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'context' => $this->sanitizeContext($context)
        ]);

        return new DocumentGenerationException(
            $userMessage,
            "Wrapped exception: " . $exception->getMessage(),
            $context,
            'UNEXPECTED_ERROR',
            500,
            $exception
        );
    }

    /**
     * Create error response array for API responses
     */
    public function createErrorResponse(
        string $errorCode,
        string $message,
        array $details = [],
        int $httpCode = 500
    ): array {
        return [
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'message' => $message,
                'details' => $details,
                'timestamp' => now()->toISOString()
            ],
            'http_code' => $httpCode
        ];
    }

    /**
     * Sanitize context for logging (remove sensitive data)
     * Requirements: 8.5 - No user data in logs
     */
    protected function sanitizeContext(array $context): array
    {
        $sensitiveKeys = [
            // User data fields
            'fields', 'user_data', 'personal_info', 'content',
            'applicant_name', 'student_name', 'declarant_name',
            'complainant_name', 'requester_name', 'appellant_name',
            'sender_name', 'address', 'phone', 'email', 'password',
            
            // Authentication and security
            'token', 'api_key', '_token', 'csrf_token', 'access_token',
            'refresh_token', 'session_token', 'auth_token',
            
            // Payment and financial
            'credit_card', 'bank_account', 'payment_info', 'billing_info',
            
            // System sensitive
            'database_password', 'db_password', 'secret', 'private_key',
            'encryption_key', 'hash', 'salt',
            
            // Request sensitive
            'password_confirmation', 'current_password', 'new_password',
            
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
                $context[$key] = $this->sanitizeContext($value);
            } elseif (is_object($value)) {
                // Convert objects to arrays for sanitization, but preserve class info
                if (method_exists($value, 'toArray')) {
                    $context[$key] = [
                        'type' => 'object',
                        'class' => get_class($value),
                        'data' => $this->sanitizeContext($value->toArray())
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
                
                // Remove potential credit card numbers
                $value = preg_replace('/\b(?:\d{4}[-\s]?){3}\d{4}\b/', '[CARD_REDACTED]', $value);
                
                $context[$key] = $value;
            }
        }

        return $context;
    }

    /**
     * Log error with appropriate level based on error type
     * Requirements: 8.5 - Logging without user data exposure
     */
    public function logError(
        string $message,
        array $context = [],
        string $level = 'error'
    ): void {
        $sanitizedContext = $this->sanitizeContext($context);
        
        // Add standard logging metadata
        $sanitizedContext['logged_at'] = now()->toISOString();
        $sanitizedContext['log_level'] = $level;
        
        // Add memory usage for performance monitoring
        $sanitizedContext['memory_usage'] = [
            'current' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true)
        ];
        
        match ($level) {
            'debug' => Log::debug($message, $sanitizedContext),
            'info' => Log::info($message, $sanitizedContext),
            'notice' => Log::notice($message, $sanitizedContext),
            'warning' => Log::warning($message, $sanitizedContext),
            'error' => Log::error($message, $sanitizedContext),
            'critical' => Log::critical($message, $sanitizedContext),
            'alert' => Log::alert($message, $sanitizedContext),
            'emergency' => Log::emergency($message, $sanitizedContext),
            default => Log::error($message, $sanitizedContext)
        };
    }

    /**
     * Log error with exception context
     */
    public function logException(
        Exception $exception,
        array $additionalContext = [],
        ?string $customMessage = null
    ): void {
        $message = $customMessage ?? $exception->getMessage();
        $context = $this->createErrorContext($exception, $additionalContext);
        $level = $this->getErrorSeverity($exception);
        
        $this->logError($message, $context, $level);
    }

    /**
     * Log performance issues
     */
    public function logPerformanceIssue(
        string $operation,
        float $executionTime,
        array $context = []
    ): void {
        if ($executionTime > 5.0) { // Log operations taking more than 5 seconds
            $this->logError("Slow operation detected: {$operation}", array_merge($context, [
                'operation' => $operation,
                'execution_time' => $executionTime,
                'performance_issue' => true
            ]), 'warning');
        }
    }

    /**
     * Log security-related events
     */
    public function logSecurityEvent(
        string $event,
        array $context = [],
        string $level = 'warning'
    ): void {
        $securityContext = array_merge($context, [
            'security_event' => true,
            'event_type' => $event,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'session_id' => request()->hasSession() ? request()->session()->getId() : null,
            'user_id' => auth()->check() ? auth()->id() : null
        ]);

        $this->logError("Security event: {$event}", $securityContext, $level);
    }

    /**
     * Check if an exception should be reported to external services
     */
    public function shouldReport(Exception $exception): bool
    {
        // Don't report validation errors or user errors
        if ($exception instanceof ValidationException ||
            $exception instanceof ContentNeutralityException ||
            $exception instanceof UnsupportedCountryException) {
            return false;
        }

        // Report system errors and unexpected exceptions
        return true;
    }

    /**
     * Get user-friendly error message based on exception type
     * Requirements: 8.5 - Clear error messages without system exposure
     */
    public function getUserFriendlyMessage(Exception $exception): string
    {
        if ($exception instanceof DocumentGenerationException) {
            return $exception->getUserMessage();
        }

        // Enhanced default messages for common Laravel exceptions
        $defaultMessages = [
            'Illuminate\Database\QueryException' => 'Erreur de base de données temporaire. Veuillez réessayer.',
            'Illuminate\Database\Eloquent\ModelNotFoundException' => 'Ressource demandée non trouvée.',
            'Illuminate\Http\Exceptions\ThrottleRequestsException' => 'Trop de requêtes. Veuillez patienter avant de réessayer.',
            'Symfony\Component\HttpKernel\Exception\NotFoundHttpException' => 'Page ou ressource non trouvée.',
            'Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException' => 'Méthode de requête non autorisée.',
            'Illuminate\Auth\AuthenticationException' => 'Authentification requise pour accéder à cette ressource.',
            'Illuminate\Auth\Access\AuthorizationException' => 'Vous n\'avez pas l\'autorisation d\'accéder à cette ressource.',
            'Illuminate\Validation\ValidationException' => 'Les données fournies ne sont pas valides.',
            'Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException' => 'Limite de requêtes dépassée. Veuillez patienter.',
            'Illuminate\Session\TokenMismatchException' => 'Session expirée. Veuillez actualiser la page et réessayer.',
            'Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException' => 'Service temporairement indisponible. Veuillez réessayer plus tard.',
            'Illuminate\Http\Exceptions\PostTooLargeException' => 'Les données envoyées sont trop volumineuses.',
            'Symfony\Component\HttpFoundation\File\Exception\FileException' => 'Erreur lors du traitement du fichier.',
            'PDOException' => 'Erreur de connexion à la base de données. Veuillez réessayer.',
            'ErrorException' => 'Erreur système temporaire. Veuillez réessayer.',
            'ParseError' => 'Erreur de traitement des données. Veuillez vérifier le format et réessayer.',
            'TypeError' => 'Erreur de type de données. Veuillez vérifier vos données et réessayer.',
            'ArgumentCountError' => 'Paramètres manquants ou incorrects.',
            'BadMethodCallException' => 'Opération non supportée.',
            'LogicException' => 'Erreur de logique interne. Veuillez contacter le support.',
            'RuntimeException' => 'Erreur d\'exécution. Veuillez réessayer.',
            'InvalidArgumentException' => 'Paramètres invalides fournis.',
            'OutOfBoundsException' => 'Données hors limites autorisées.',
            'OverflowException' => 'Capacité dépassée. Veuillez réduire la taille des données.',
            'UnderflowException' => 'Données insuffisantes pour l\'opération.',
            'UnexpectedValueException' => 'Valeur inattendue rencontrée. Veuillez vérifier vos données.',
            'DomainException' => 'Opération non valide dans ce contexte.',
            'LengthException' => 'Longueur de données invalide.',
            'RangeException' => 'Valeur hors de la plage autorisée.'
        ];

        $exceptionClass = get_class($exception);
        
        return $defaultMessages[$exceptionClass] ?? 
               "Une erreur inattendue s'est produite. Veuillez réessayer.";
    }

    /**
     * Get error severity level based on exception type
     */
    public function getErrorSeverity(Exception $exception): string
    {
        // Critical system errors
        if ($exception instanceof \PDOException ||
            $exception instanceof \Illuminate\Database\QueryException ||
            $exception instanceof \ErrorException) {
            return 'critical';
        }

        // High priority errors
        if ($exception instanceof DataIntegrityException ||
            $exception instanceof SessionDataCorruptionException ||
            $exception instanceof UserContentModificationException) {
            return 'error';
        }

        // Medium priority errors
        if ($exception instanceof TemplateException ||
            $exception instanceof OutputFormattingException ||
            $exception instanceof CountryAdaptationException) {
            return 'warning';
        }

        // Low priority errors (user errors)
        if ($exception instanceof ValidationException ||
            $exception instanceof ContentNeutralityException ||
            $exception instanceof UnsupportedCountryException) {
            return 'info';
        }

        // Default to error for unknown exceptions
        return 'error';
    }

    /**
     * Create comprehensive error context for logging
     */
    public function createErrorContext(
        Exception $exception,
        array $additionalContext = []
    ): array {
        $context = [
            'exception_class' => get_class($exception),
            'exception_code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'severity' => $this->getErrorSeverity($exception),
            'trace_hash' => md5($exception->getTraceAsString()),
            'timestamp' => now()->toISOString()
        ];

        // Add request context if available
        if (app()->bound('request')) {
            $request = request();
            $context['request'] = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                'user_id' => auth()->check() ? auth()->id() : null
            ];
        }

        // Add previous exception context
        if ($exception->getPrevious()) {
            $context['previous_exception'] = [
                'class' => get_class($exception->getPrevious()),
                'message' => $exception->getPrevious()->getMessage(),
                'file' => $exception->getPrevious()->getFile(),
                'line' => $exception->getPrevious()->getLine()
            ];
        }

        // Merge additional context and sanitize
        $context = array_merge($context, $additionalContext);
        return $this->sanitizeContext($context);
    }
}