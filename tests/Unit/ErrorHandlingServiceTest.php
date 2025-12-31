<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ErrorHandlingService;
use App\Exceptions\ValidationException;
use App\Exceptions\ContentNeutralityException;
use App\Exceptions\DocumentGenerationException;
use Exception;
use Illuminate\Support\Facades\Log;

class ErrorHandlingServiceTest extends TestCase
{
    private ErrorHandlingService $errorHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->errorHandler = new ErrorHandlingService();
    }

    public function test_handles_validation_errors()
    {
        $validationErrors = ['field1' => 'required', 'field2' => 'invalid format'];
        
        $exception = $this->errorHandler->handleValidationError(
            'Validation failed',
            $validationErrors,
            ['context' => 'test']
        );

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertEquals('Validation failed', $exception->getUserMessage());
        $this->assertEquals($validationErrors, $exception->getValidationErrors());
        $this->assertEquals('VALIDATION_ERROR', $exception->getErrorCode());
        $this->assertEquals(422, $exception->getCode());
    }

    public function test_handles_content_neutrality_violations()
    {
        $violations = [
            'legal_advice' => [
                ['matched_text' => 'vous devriez', 'context' => 'test context']
            ]
        ];

        $exception = $this->errorHandler->handleContentNeutralityViolation(
            'Content neutrality violation',
            $violations,
            ['test' => 'context']
        );

        $this->assertInstanceOf(ContentNeutralityException::class, $exception);
        $this->assertEquals('Content neutrality violation', $exception->getUserMessage());
        $this->assertEquals($violations, $exception->getViolations());
        $this->assertEquals('CONTENT_NEUTRALITY_VIOLATION', $exception->getErrorCode());
        $this->assertEquals(400, $exception->getCode());
    }

    public function test_wraps_unexpected_exceptions()
    {
        Log::shouldReceive('error')->once();

        $originalException = new Exception('Original error message');
        
        $exception = $this->errorHandler->wrapUnexpectedException(
            $originalException,
            'User friendly message',
            ['context' => 'test']
        );

        $this->assertInstanceOf(DocumentGenerationException::class, $exception);
        $this->assertEquals('User friendly message', $exception->getUserMessage());
        $this->assertEquals('UNEXPECTED_ERROR', $exception->getErrorCode());
        $this->assertEquals(500, $exception->getCode());
        $this->assertEquals($originalException, $exception->getPrevious());
    }

    public function test_creates_error_response_array()
    {
        $response = $this->errorHandler->createErrorResponse(
            'TEST_ERROR',
            'Test message',
            ['detail1' => 'value1'],
            400
        );

        $this->assertFalse($response['success']);
        $this->assertEquals('TEST_ERROR', $response['error']['code']);
        $this->assertEquals('Test message', $response['error']['message']);
        $this->assertEquals(['detail1' => 'value1'], $response['error']['details']);
        $this->assertEquals(400, $response['http_code']);
        $this->assertArrayHasKey('timestamp', $response['error']);
    }

    public function test_sanitizes_sensitive_context_data()
    {
        $sensitiveContext = [
            'fields' => ['applicant_name' => 'John Doe', 'email' => 'john@example.com'],
            'password' => 'secret123',
            'safe_data' => 'this is safe',
            'nested' => [
                'user_data' => 'sensitive',
                'safe_nested' => 'safe'
            ]
        ];

        // Use reflection to access the protected method
        $reflection = new \ReflectionClass($this->errorHandler);
        $method = $reflection->getMethod('sanitizeContext');
        $method->setAccessible(true);

        $sanitized = $method->invoke($this->errorHandler, $sensitiveContext);

        $this->assertEquals('[REDACTED]', $sanitized['fields']['data']);
        $this->assertEquals('[REDACTED]', $sanitized['password']);
        $this->assertEquals('this is safe', $sanitized['safe_data']);
        $this->assertEquals('[REDACTED]', $sanitized['nested']['user_data']);
        $this->assertEquals('safe', $sanitized['nested']['safe_nested']);
    }

    public function test_determines_reportable_exceptions()
    {
        $validationException = new ValidationException('Validation error');
        $neutralityException = new ContentNeutralityException('Neutrality violation');
        $systemException = new Exception('System error');

        $this->assertFalse($this->errorHandler->shouldReport($validationException));
        $this->assertFalse($this->errorHandler->shouldReport($neutralityException));
        $this->assertTrue($this->errorHandler->shouldReport($systemException));
    }

    public function test_gets_user_friendly_messages()
    {
        $documentException = new ValidationException('Validation failed');
        $systemException = new Exception('System error');
        $databaseException = new \Illuminate\Database\QueryException('', '', [], new Exception());

        $this->assertEquals('Validation failed', $this->errorHandler->getUserFriendlyMessage($documentException));
        $this->assertStringContainsString('erreur inattendue', $this->errorHandler->getUserFriendlyMessage($systemException));
        $this->assertStringContainsString('base de données temporaire', $this->errorHandler->getUserFriendlyMessage($databaseException));
    }

    public function test_gets_error_severity_levels()
    {
        $validationException = new ValidationException('Validation error');
        $dataIntegrityException = new \App\Exceptions\DataIntegrityException('Data integrity error');
        $templateException = new \App\Exceptions\TemplateException('Template error');
        $systemException = new \PDOException('Database error');

        // Use reflection to access the protected method
        $reflection = new \ReflectionClass($this->errorHandler);
        $method = $reflection->getMethod('getErrorSeverity');
        $method->setAccessible(true);

        $this->assertEquals('info', $method->invoke($this->errorHandler, $validationException));
        $this->assertEquals('error', $method->invoke($this->errorHandler, $dataIntegrityException));
        $this->assertEquals('warning', $method->invoke($this->errorHandler, $templateException));
        $this->assertEquals('critical', $method->invoke($this->errorHandler, $systemException));
    }

    public function test_creates_comprehensive_error_context()
    {
        $exception = new Exception('Test exception');
        
        $context = $this->errorHandler->createErrorContext($exception, ['additional' => 'data']);

        $this->assertEquals(Exception::class, $context['exception_class']);
        $this->assertEquals('Test exception', $exception->getMessage());
        $this->assertArrayHasKey('file', $context);
        $this->assertArrayHasKey('line', $context);
        $this->assertArrayHasKey('severity', $context);
        $this->assertArrayHasKey('trace_hash', $context);
        $this->assertArrayHasKey('timestamp', $context);
        $this->assertEquals('data', $context['additional']);
    }

    public function test_logs_exceptions_with_context()
    {
        Log::shouldReceive('error')->once()->with(
            'Test exception',
            \Mockery::on(function ($context) {
                return isset($context['exception_class']) &&
                       isset($context['severity']) &&
                       isset($context['trace_hash']) &&
                       isset($context['timestamp']);
            })
        );

        $exception = new Exception('Test exception');
        $this->errorHandler->logException($exception);
    }

    public function test_logs_performance_issues()
    {
        Log::shouldReceive('warning')->once()->with(
            'Slow operation detected: test_operation',
            \Mockery::on(function ($context) {
                return $context['operation'] === 'test_operation' &&
                       $context['execution_time'] === 6.0 &&
                       $context['performance_issue'] === true;
            })
        );

        $this->errorHandler->logPerformanceIssue('test_operation', 6.0);
    }

    public function test_logs_security_events()
    {
        Log::shouldReceive('warning')->once()->with(
            'Security event: suspicious_activity',
            \Mockery::on(function ($context) {
                return $context['security_event'] === true &&
                       $context['event_type'] === 'suspicious_activity';
            })
        );

        $this->errorHandler->logSecurityEvent('suspicious_activity');
    }

    public function test_logs_errors_with_different_levels()
    {
        Log::shouldReceive('warning')->once()->with('Test warning', \Mockery::type('array'));
        Log::shouldReceive('error')->once()->with('Test error', \Mockery::type('array'));

        $this->errorHandler->logError('Test warning', ['context' => 'test'], 'warning');
        $this->errorHandler->logError('Test error', ['context' => 'test'], 'error');
    }

    public function test_handles_generic_document_generation_errors()
    {
        $exception = $this->errorHandler->handleDocumentGenerationError(
            'User message',
            'Internal message',
            ['context' => 'test'],
            'CUSTOM_ERROR',
            400
        );

        $this->assertInstanceOf(DocumentGenerationException::class, $exception);
        $this->assertEquals('User message', $exception->getUserMessage());
        $this->assertEquals('CUSTOM_ERROR', $exception->getErrorCode());
        $this->assertEquals(400, $exception->getCode());
    }
}