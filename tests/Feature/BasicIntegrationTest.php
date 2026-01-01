<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BasicIntegrationTest extends TestCase
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
    }

    public function test_api_health_check_endpoint()
    {
        $this->actingAs($this->user);
        
        $response = $this->getJson('/api/documents/health');
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'status',
            'checks' => [
                'database',
                'cache',
                'storage'
            ],
            'timestamp'
        ]);
    }

    public function test_authenticated_user_can_access_document_types()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('api.documents.types'));
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['key', 'value', 'label', 'required_fields']
            ]
        ]);
    }

    public function test_unauthenticated_user_cannot_access_document_types()
    {
        $response = $this->getJson(route('api.documents.types'));
        $response->assertUnauthorized();
    }

    public function test_authenticated_user_can_access_supported_countries()
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('api.documents.countries'));
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['key', 'value', 'label', 'supported']
            ]
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
    }

    public function test_document_generator_livewire_component_loads()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('document-generator'));
        $response->assertSuccessful();
        $response->assertSeeLivewire(\App\Livewire\DocumentGenerator::class);
    }
}