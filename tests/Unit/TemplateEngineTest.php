<?php

namespace Tests\Unit;

use App\Services\TemplateEngine;
use App\Models\DocumentTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use InvalidArgumentException;

class TemplateEngineTest extends TestCase
{
    use RefreshDatabase;

    private TemplateEngine $templateEngine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->templateEngine = app(TemplateEngine::class);
        
        // Seed the database with test data
        $this->seed(\Database\Seeders\DocumentTemplatesSeeder::class);
    }

    public function test_can_get_template_from_database()
    {
        $template = $this->templateEngine->getTemplate('administrative_application', 'SN', 'fr');
        
        $this->assertInstanceOf(DocumentTemplate::class, $template);
        $this->assertEquals('administrative_application', $template->document_type);
        $this->assertEquals('SN', $template->country);
        $this->assertEquals('fr', $template->language);
    }

    public function test_can_render_administrative_application_template()
    {
        $data = [
            'sender_name' => 'Jean Dupont',
            'sender_address' => '123 Rue de la Paix',
            'sender_city' => 'Dakar',
            'recipient_name' => 'Monsieur le Directeur',
            'recipient_organization' => 'Ministère de l\'Intérieur',
            'subject' => 'Demande de carte d\'identité',
            'request_context' => 'Je sollicite par la présente une carte d\'identité nationale.',
            'request_details' => 'Ma précédente carte a été perdue lors d\'un voyage.'
        ];

        $result = $this->templateEngine->renderTemplate('administrative_application', $data, 'SN', 'fr');
        
        $this->assertIsString($result);
        $this->assertStringContainsString('Jean Dupont', $result);
        // Account for HTML entities in the output
        $this->assertTrue(
            str_contains($result, 'Demande de carte d\'identité') || 
            str_contains($result, 'Demande de carte d&#039;identité')
        );
        $this->assertTrue(
            str_contains($result, 'Ministère de l\'Intérieur') || 
            str_contains($result, 'Ministère de l&#039;Intérieur')
        );
    }

    public function test_throws_exception_for_missing_required_fields()
    {
        $data = [
            'sender_name' => 'Jean Dupont',
            // Missing required fields
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required fields');
        
        $this->templateEngine->renderTemplate('administrative_application', $data, 'SN', 'fr');
    }

    public function test_throws_exception_for_unsupported_document_type()
    {
        $data = ['test' => 'data'];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Template not found');
        
        $this->templateEngine->renderTemplate('unsupported_type', $data, 'SN', 'fr');
    }

    public function test_can_validate_template_data()
    {
        $validData = [
            'sender_name' => 'Jean Dupont',
            'sender_address' => '123 Rue de la Paix',
            'sender_city' => 'Dakar',
            'recipient_name' => 'Monsieur le Directeur',
            'recipient_organization' => 'Ministère de l\'Intérieur',
            'subject' => 'Demande de carte d\'identité',
            'request_context' => 'Je sollicite par la présente une carte d\'identité nationale.',
            'request_details' => 'Ma précédente carte a été perdue lors d\'un voyage.',
            'sender_email' => 'jean@example.com'
        ];

        $errors = $this->templateEngine->validateTemplateData('administrative_application', $validData, 'SN', 'fr');
        
        $this->assertEmpty($errors);
    }

    public function test_validation_catches_invalid_email()
    {
        $invalidData = [
            'sender_name' => 'Jean Dupont',
            'sender_address' => '123 Rue de la Paix',
            'sender_city' => 'Dakar',
            'recipient_name' => 'Monsieur le Directeur',
            'recipient_organization' => 'Ministère de l\'Intérieur',
            'subject' => 'Demande de carte d\'identité',
            'request_context' => 'Je sollicite par la présente une carte d\'identité nationale.',
            'request_details' => 'Ma précédente carte a été perdue lors d\'un voyage.',
            'sender_email' => 'invalid-email'
        ];

        $errors = $this->templateEngine->validateTemplateData('administrative_application', $invalidData, 'SN', 'fr');
        
        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('email', $errors[0]);
    }

    public function test_can_get_supported_document_types()
    {
        $types = $this->templateEngine->getSupportedDocumentTypes();
        
        $this->assertIsArray($types);
        $this->assertContains('administrative_application', $types);
        $this->assertContains('motivation_letter', $types);
    }

    public function test_can_check_if_document_type_is_supported()
    {
        $this->assertTrue($this->templateEngine->isDocumentTypeSupported('administrative_application'));
        $this->assertFalse($this->templateEngine->isDocumentTypeSupported('unsupported_type'));
    }

    public function test_can_render_template_for_export_without_html_entities()
    {
        $data = [
            'sender_name' => 'Jean Dupont',
            'sender_address' => '123 Rue de la Paix',
            'sender_city' => 'Dakar',
            'recipient_name' => 'Monsieur le Directeur',
            'recipient_organization' => 'Ministère de l\'Intérieur',
            'subject' => 'Demande de carte d\'identité',
            'request_context' => 'Je sollicite par la présente une carte d\'identité nationale.',
            'request_details' => 'Ma précédente carte a été perdue lors d\'un voyage.'
        ];

        $result = $this->templateEngine->renderTemplateForExport('administrative_application', $data, 'SN', 'fr');
        
        $this->assertIsString($result);
        $this->assertStringContainsString('Jean Dupont', $result);
        // Should contain actual apostrophes, not HTML entities
        $this->assertStringContainsString('Demande de carte d\'identité', $result);
        $this->assertStringContainsString('Ministère de l\'Intérieur', $result);
        // Should not contain HTML entities
        $this->assertStringNotContainsString('&#039;', $result);
    }

    public function test_sanitizes_output_correctly()
    {
        $data = [
            'sender_name' => 'Jean Dupont',
            'sender_address' => '123 Rue de la Paix',
            'sender_city' => 'Dakar',
            'recipient_name' => 'Monsieur le Directeur',
            'recipient_organization' => 'Ministère de l\'Intérieur',
            'subject' => 'Demande de carte d\'identité',
            'request_context' => 'Je sollicite par la présente une carte d\'identité nationale.',
            'request_details' => 'Ma précédente carte a été perdue lors d\'un voyage.'
        ];

        $result = $this->templateEngine->renderTemplate('administrative_application', $data, 'SN', 'fr');
        
        // Should not contain HTML comments
        $this->assertStringNotContainsString('<!--', $result);
        $this->assertStringNotContainsString('-->', $result);
        
        // Should not have excessive empty lines
        $this->assertStringNotContainsString("\n\n\n", $result);
    }
}