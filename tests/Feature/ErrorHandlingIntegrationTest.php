<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\DocumentGeneratorService;
use App\Services\ErrorHandlingService;
use App\Data\DocumentGenerationRequest;
use App\Exceptions\ValidationException;
use App\Exceptions\ContentNeutralityException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class ErrorHandlingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed the database with required data
        $this->artisan('db:seed', ['--class' => 'DocumentTemplatesSeeder']);
        $this->artisan('db:seed', ['--class' => 'CountryRulesSeeder']);
    }

    public function test_document_generator_throws_validation_exception_for_invalid_request()
    {
        $this->expectException(ValidationException::class);

        $documentGenerator = app(DocumentGeneratorService::class);
        
        // Create an invalid request (missing required fields)
        $invalidRequest = new DocumentGenerationRequest(
            country: 'INVALID_COUNTRY',
            document_type: 'ADMINISTRATIVE_APPLICATION',
            tone_level: 'OFFICIAL',
            language: 'fr',
            fields: [] // Missing required fields
        );

        try {
            $documentGenerator->generateDocument($invalidRequest);
        } catch (ValidationException $e) {
            // Verify that the exception contains validation errors
            $this->assertStringContainsString('Pays non supporté', $e->getMessage());
            $this->assertStringContainsString('Champs obligatoires manquants', $e->getMessage());
            throw $e; // Re-throw to satisfy expectException
        }
    }

    public function test_error_handling_service_creates_proper_error_responses()
    {
        $errorHandler = app(ErrorHandlingService::class);
        
        $response = $errorHandler->createErrorResponse(
            'TEST_ERROR',
            'Test error message',
            ['field' => 'value'],
            400
        );

        $this->assertFalse($response['success']);
        $this->assertEquals('TEST_ERROR', $response['error']['code']);
        $this->assertEquals('Test error message', $response['error']['message']);
        $this->assertEquals(['field' => 'value'], $response['error']['details']);
        $this->assertEquals(400, $response['http_code']);
        $this->assertArrayHasKey('timestamp', $response['error']);
    }

    public function test_error_handling_service_sanitizes_sensitive_data_in_logs()
    {
        Log::shouldReceive('error')->once()->with(
            'Test error',
            \Mockery::on(function ($context) {
                // Verify that sensitive data is redacted and new metadata is added
                return $context['fields']['data'] === '[REDACTED]' &&
                       $context['password'] === '[REDACTED]' &&
                       $context['safe_data'] === 'safe' &&
                       isset($context['logged_at']) &&
                       isset($context['log_level']) &&
                       isset($context['memory_usage']);
            })
        );

        $errorHandler = app(ErrorHandlingService::class);
        
        $errorHandler->logError('Test error', [
            'fields' => ['applicant_name' => 'John Doe'],
            'password' => 'secret123',
            'safe_data' => 'safe'
        ]);
    }

    public function test_validation_exception_renders_proper_json_response()
    {
        $validationErrors = ['field1' => 'required', 'field2' => 'invalid'];
        $exception = new ValidationException('Validation failed', $validationErrors);
        
        $request = request();
        $response = $exception->render($request);
        
        $this->assertEquals(422, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('VALIDATION_ERROR', $responseData['error']['code']);
        $this->assertEquals('Validation failed', $responseData['error']['message']);
        $this->assertEquals($validationErrors, $responseData['error']['validation_errors']);
        $this->assertArrayHasKey('timestamp', $responseData['error']);
    }

    public function test_content_neutrality_exception_renders_proper_json_response()
    {
        $violations = [
            'legal_advice' => [
                ['matched_text' => 'vous devriez', 'context' => 'test context']
            ]
        ];
        
        $exception = new ContentNeutralityException('Content violation', $violations);
        
        $request = request();
        $response = $exception->render($request);
        
        $this->assertEquals(400, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('CONTENT_NEUTRALITY_VIOLATION', $responseData['error']['code']);
        $this->assertEquals('Content violation', $responseData['error']['message']);
        $this->assertArrayHasKey('violations', $responseData['error']);
        $this->assertArrayHasKey('timestamp', $responseData['error']);
    }

    public function test_error_handler_determines_reportable_exceptions_correctly()
    {
        $errorHandler = app(ErrorHandlingService::class);
        
        // User errors should not be reported
        $validationException = new ValidationException('Validation error');
        $neutralityException = new ContentNeutralityException('Neutrality violation');
        
        $this->assertFalse($errorHandler->shouldReport($validationException));
        $this->assertFalse($errorHandler->shouldReport($neutralityException));
        
        // System errors should be reported
        $systemException = new \Exception('System error');
        $this->assertTrue($errorHandler->shouldReport($systemException));
    }
}