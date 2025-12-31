<?php

namespace App\Console\Commands;

use App\Services\DocumentGeneratorService;
use App\Data\DocumentGenerationRequest;
use App\Models\DocumentTemplate;
use App\Models\CountryRule;
use Illuminate\Console\Command;

class TestDocumentGenerator extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:document-generator';

    /**
     * The console command description.
     */
    protected $description = 'Test the DocumentGeneratorService with sample data';

    /**
     * Execute the console command.
     */
    public function handle(DocumentGeneratorService $service): int
    {
        $this->info('Testing DocumentGeneratorService...');

        // Test 1: Get supported types
        $this->info('1. Testing getSupportedTypes()...');
        $types = $service->getSupportedTypes();
        $this->info("Found {$types->count()} supported document types:");
        foreach ($types as $type) {
            $this->line("  - {$type['key']}: {$type['label']}");
        }

        // Test 2: Get supported countries
        $this->info('2. Testing getSupportedCountries()...');
        $countries = $service->getSupportedCountries();
        $this->info("Found {$countries->count()} supported countries:");
        foreach ($countries as $country) {
            $this->line("  - {$country['key']}: {$country['label']}");
        }

        // Test 3: Get supported tone levels
        $this->info('3. Testing getSupportedToneLevels()...');
        $toneLevels = $service->getSupportedToneLevels();
        $this->info("Found {$toneLevels->count()} supported tone levels:");
        foreach ($toneLevels as $tone) {
            $this->line("  - {$tone['key']}: {$tone['label']}");
        }

        // Test 4: Create a sample request
        $this->info('4. Testing document generation with sample data...');
        
        // Ensure we have test data
        $this->ensureTestData();

        $request = new DocumentGenerationRequest(
            country: 'SN',
            document_type: 'administrative_application',
            tone_level: 'official',
            language: 'fr',
            fields: [
                'applicant_name' => 'Jean Dupont',
                'applicant_address' => '123 Rue de la Paix, Dakar, Sénégal',
                'recipient_title' => 'Monsieur le Directeur',
                'subject' => 'Demande de certificat de résidence',
                'request_details' => 'Je sollicite par la présente un certificat de résidence pour les besoins de ma demande de visa.',
                'date' => now()->format('Y-m-d')
            ],
            session_id: 'test_console_' . time()
        );

        // Test preview
        $this->info('Testing preview generation...');
        $previewResult = $service->previewDocument($request);
        
        if ($previewResult->success) {
            $this->info('✓ Preview generation successful');
            $this->line('Preview content length: ' . strlen($previewResult->content) . ' characters');
        } else {
            $this->error('✗ Preview generation failed: ' . $previewResult->error_message);
            if (!empty($previewResult->errors)) {
                foreach ($previewResult->errors as $error) {
                    $this->error("  - $error");
                }
            }
        }

        // Test full generation
        $this->info('Testing full document generation...');
        $result = $service->generateDocument($request);
        
        if ($result->success) {
            $this->info('✓ Document generation successful');
            $this->line('Session ID: ' . $result->session_id);
            $this->line('Document ID: ' . $result->document_id);
            $this->line('Content length: ' . strlen($result->content) . ' characters');
            $this->line('Metadata: ' . json_encode($result->metadata, JSON_PRETTY_PRINT));
        } else {
            $this->error('✗ Document generation failed: ' . $result->error_message);
            if (!empty($result->errors)) {
                foreach ($result->errors as $error) {
                    $this->error("  - $error");
                }
            }
        }

        $this->info('DocumentGeneratorService testing completed.');
        return Command::SUCCESS;
    }

    /**
     * Ensure test data exists
     */
    private function ensureTestData(): void
    {
        // Create DocumentTemplate if it doesn't exist
        if (!DocumentTemplate::where('document_type', 'administrative_application')
            ->where('country', 'SN')
            ->where('language', 'fr')
            ->exists()) {
            
            DocumentTemplate::create([
                'document_type' => 'administrative_application',
                'country' => 'SN',
                'language' => 'fr',
                'template_content' => 'Test template content',
                'required_fields' => ['applicant_name', 'applicant_address', 'recipient_title', 'subject', 'request_details', 'date'],
                'optional_fields' => [],
                'validation_rules' => [],
                'is_active' => true,
            ]);
            
            $this->line('Created test DocumentTemplate');
        }

        // Create CountryRule if it doesn't exist
        if (!CountryRule::where('country_code', 'SN')->exists()) {
            CountryRule::create([
                'country_code' => 'SN',
                'country_name' => 'Sénégal',
                'politeness_formulas' => [
                    'simple' => [
                        'opening' => 'Monsieur/Madame,',
                        'closing' => 'Cordialement,'
                    ],
                    'official' => [
                        'opening' => 'Monsieur/Madame le/la Responsable,',
                        'closing' => 'Veuillez agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.'
                    ],
                    'very_official' => [
                        'opening' => 'Excellence,',
                        'closing' => 'Veuillez agréer, Excellence, l\'expression de ma très haute considération.'
                    ]
                ],
                'date_format' => 'd/m/Y',
                'address_format' => ['template' => '{street}, {city}, {country}'],
                'administrative_terminology' => [],
                'document_structure_rules' => [],
                'default_language' => 'fr',
                'is_active' => true,
            ]);
            
            $this->line('Created test CountryRule');
        }
    }
}