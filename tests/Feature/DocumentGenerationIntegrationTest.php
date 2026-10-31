<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentGenerationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::factory()->create();

        // Seed the database with required data
        $this->artisan('db:seed', ['--class' => 'DocumentTemplatesSeeder']);
        $this->artisan('db:seed', ['--class' => 'CountryRulesSeeder']);

        // Clear cache before each test
        Cache::flush();

        // Set up fake storage
        Storage::fake('local');
    }

    public function test_complete_document_generation_workflow()
    {
        $this->actingAs($this->user);

        // Step 1: Get supported document types
        $typesResponse = $this->getJson(route('api.documents.types'));
        $typesResponse->assertSuccessful();
        $typesResponse->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['key', 'value', 'label', 'required_fields'],
            ],
        ]);

        // Step 2: Get supported countries
        $countriesResponse = $this->getJson(route('api.documents.countries'));
        $countriesResponse->assertSuccessful();
        $countriesResponse->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['key', 'value', 'label', 'supported'],
            ],
        ]);

        // Step 3: Get tone levels
        $toneLevelsResponse = $this->getJson(route('api.documents.tone-levels'));
        $toneLevelsResponse->assertSuccessful();
        $toneLevelsResponse->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['key', 'value', 'label'],
            ],
        ]);

        // Step 4: Generate document preview
        $previewData = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => [
                'applicant_name' => 'Jean Dupont',
                'applicant_address' => '123 Rue de la Paix, Dakar',
                'applicant_city' => 'Dakar',
                'applicant_phone' => '+221 77 123 45 67',
                'applicant_email' => 'jean.dupont@email.com',
                'recipient_title' => 'Monsieur le Directeur',
                'recipient_name' => 'Amadou Diallo',
                'recipient_organization' => 'Ministère de l\'Administration',
                'subject' => 'Demande de certificat de résidence',
                'request_context' => 'Demande administrative pour obtenir un certificat de résidence',
                'request_details' => 'Je sollicite par la présente un certificat de résidence.',
                'date' => '2024-01-15',
            ],
        ];

        $previewResponse = $this->postJson(route('api.documents.preview'), $previewData);
        $previewResponse->assertSuccessful();
        $previewResponse->assertJsonStructure([
            'success',
            'data' => [
                'content',
                'metadata',
                'session_id',
            ],
        ]);

        $previewContent = $previewResponse->json('data.content');
        $this->assertStringContainsString('Jean Dupont', $previewContent);
        $this->assertStringContainsString('Demande de certificat de résidence', $previewContent);

        // Step 5: Generate complete document
        $generateResponse = $this->postJson(route('api.documents.generate'), $previewData);
        $generateResponse->assertSuccessful();
        $generateResponse->assertJsonStructure([
            'success',
            'data' => [
                'content',
                'document_id',
                'session_id',
                'metadata',
                'download_url',
            ],
        ]);

        $documentId = $generateResponse->json('data.document_id');
        $sessionId = $generateResponse->json('data.session_id');
        $downloadUrl = $generateResponse->json('data.download_url');

        // Verify document is cached
        $cacheKey = "document:{$sessionId}:{$documentId}";
        $this->assertTrue(Cache::has($cacheKey));

        // Step 6: Download PDF
        $downloadResponse = $this->get($downloadUrl);
        $downloadResponse->assertSuccessful();
        $downloadResponse->assertHeader('Content-Type', 'application/pdf');

        // Verify PDF file was created in storage
        $this->assertTrue(Storage::disk('local')->exists('documents/'.date('Y/m/d')));
    }

    public function test_document_generation_with_validation_errors()
    {
        $this->actingAs($this->user);

        // Test with missing required fields
        $invalidData = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => [
                'applicant_name' => 'Jean Dupont',
                'applicant_city' => 'Dakar',
                'request_context' => 'Test context',
                // Missing other required fields
            ],
        ];

        $response = $this->postJson(route('api.documents.generate'), $invalidData);
        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'error' => [
                'code',
                'message',
                'validation_errors',
            ],
        ]);
    }

    public function test_document_generation_with_unsupported_country()
    {
        $this->actingAs($this->user);

        $invalidData = [
            'country' => 'INVALID_COUNTRY',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => [
                'applicant_name' => 'Jean Dupont',
                'applicant_address' => '123 Rue de la Paix',
                'applicant_city' => 'Dakar',
                'applicant_phone' => '+221 77 123 45 67',
                'applicant_email' => 'jean.dupont@email.com',
                'recipient_title' => 'Monsieur le Directeur',
                'recipient_name' => 'Amadou Diallo',
                'recipient_organization' => 'Ministère',
                'subject' => 'Test',
                'request_context' => 'Test context',
                'request_details' => 'Test request',
                'date' => '2024-01-15',
            ],
        ];

        $response = $this->postJson(route('api.documents.generate'), $invalidData);
        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_rate_limiting_on_document_generation()
    {
        $this->actingAs($this->user);

        $validData = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => [
                'applicant_name' => 'Jean Dupont',
                'applicant_address' => '123 Rue de la Paix, Dakar',
                'applicant_city' => 'Dakar',
                'applicant_phone' => '+221 77 123 45 67',
                'applicant_email' => 'jean.dupont@email.com',
                'recipient_title' => 'Monsieur le Directeur',
                'recipient_name' => 'Amadou Diallo',
                'recipient_organization' => 'Ministère de l\'Administration',
                'subject' => 'Demande de certificat',
                'request_context' => 'Demande administrative pour certificat',
                'request_details' => 'Je sollicite un certificat.',
                'date' => '2024-01-15',
            ],
        ];

        // Make requests up to the rate limit (10 per minute)
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson(route('api.documents.preview'), $validData);
            $response->assertSuccessful();
        }

        // The 11th request should be rate limited
        $response = $this->postJson(route('api.documents.preview'), $validData);
        $response->assertStatus(429);
        $response->assertJsonPath('error.code', 'RATE_LIMIT_EXCEEDED');
    }

    public function test_unauthorized_document_download()
    {
        $this->actingAs($this->user);

        // Try to download with wrong session ID
        $response = $this->get(route('documents.download', [
            'session' => 'wrong_session_id',
            'document' => 'some_document_id',
        ]));

        $response->assertStatus(403);
        $response->assertJsonPath('error.code', 'UNAUTHORIZED_ACCESS');
    }

    public function test_document_download_not_found()
    {
        $this->actingAs($this->user);

        // Try to download non-existent document
        $response = $this->get(route('documents.download', [
            'session' => session()->getId(),
            'document' => 'non_existent_document',
        ]));

        $response->assertStatus(404);
        $response->assertJsonPath('error.code', 'DOCUMENT_NOT_FOUND');
    }

    public function test_health_check_endpoint()
    {
        $response = $this->getJson(route('api.documents.health'));
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'status',
            'checks' => [
                'database',
                'cache',
                'storage',
            ],
            'timestamp',
        ]);
    }

    public function test_security_headers_are_applied()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('document-generator'));

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeaderMissing('Server'); // Should not expose server info
    }

    public function test_caching_optimization()
    {
        $this->actingAs($this->user);

        // First request should hit the database
        $response1 = $this->getJson(route('api.documents.types'));
        $response1->assertSuccessful();

        // Second request should hit the cache
        $response2 = $this->getJson(route('api.documents.types'));
        $response2->assertSuccessful();

        // Verify cache is being used
        $this->assertTrue(Cache::has('document_types'));
    }

    public function test_error_handling_across_components()
    {
        $this->actingAs($this->user);

        // Test with data that will cause content neutrality violation
        $problematicData = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => [
                'applicant_name' => 'Jean Dupont',
                'applicant_address' => '123 Rue de la Paix, Dakar',
                'applicant_city' => 'Dakar',
                'applicant_phone' => '+221 77 123 45 67',
                'applicant_email' => 'jean.dupont@email.com',
                'recipient_title' => 'Monsieur le Directeur',
                'recipient_name' => 'Amadou Diallo',
                'recipient_organization' => 'Ministère de l\'Administration',
                'subject' => 'Demande de certificat',
                'request_context' => 'Demande administrative pour certificat',
                'request_details' => 'Vous devriez accepter ma demande car j\'ai raison légalement.',
                'date' => '2024-01-15',
            ],
        ];

        $response = $this->postJson(route('api.documents.generate'), $problematicData);

        // Should handle content neutrality violation gracefully
        $response->assertStatus(400);
        $response->assertJsonPath('error.code', 'CONTENT_NEUTRALITY_VIOLATION');
    }

    public function test_session_data_isolation()
    {
        // Create two different users
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $documentData = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => [
                'applicant_name' => 'User Test',
                'applicant_address' => '123 Test Street',
                'applicant_city' => 'Dakar',
                'applicant_phone' => '+221 77 123 45 67',
                'applicant_email' => 'test@email.com',
                'recipient_title' => 'Monsieur le Directeur',
                'recipient_name' => 'Test Recipient',
                'recipient_organization' => 'Test Organization',
                'subject' => 'Test Subject',
                'request_context' => 'Test context for administrative request',
                'request_details' => 'Test request details',
                'date' => '2024-01-15',
            ],
        ];

        // Generate document as user1
        $this->actingAs($user1);
        $response1 = $this->postJson(route('api.documents.generate'), $documentData);
        $response1->assertSuccessful();

        $documentId1 = $response1->json('data.document_id');
        $sessionId1 = $response1->json('data.session_id');

        // Try to access user1's document as user2
        $this->actingAs($user2);
        $response2 = $this->get(route('documents.download', [
            'session' => $sessionId1,
            'document' => $documentId1,
        ]));

        // Should be denied due to session isolation
        $response2->assertStatus(403);
    }
}
