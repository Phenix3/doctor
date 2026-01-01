<?php

namespace App\Console\Commands;

use App\Services\TemplateEngine;
use Illuminate\Console\Command;

class TestDocumentTypes extends Command
{
    protected $signature = 'test:document-types';
    protected $description = 'Test all document types with sample data';

    public function handle()
    {
        $engine = app(TemplateEngine::class);
        
        $this->info('Testing Document Types Implementation');
        $this->info('=====================================');
        
        // Test Administrative Application
        $this->info('1. Testing Administrative Application...');
        $data = [
            'applicant_name' => 'Jean Dupont',
            'applicant_address' => '123 Rue de la Paix',
            'applicant_city' => 'Dakar',
            'recipient_title' => 'Monsieur le Directeur',
            'recipient_organization' => 'Ministère de l\'Éducation',
            'subject' => 'Demande de bourse d\'études',
            'request_context' => 'une bourse d\'études supérieures',
            'request_details' => 'Je sollicite une bourse pour poursuivre mes études en informatique.',
            'date' => '2024-01-15'
        ];
        
        try {
            $result = $engine->renderTemplate('administrative_application', $data, 'SN', 'fr');
            $this->info('✓ Administrative Application template rendered successfully');
            $this->line('Sample output (first 200 chars): ' . substr($result, 0, 200) . '...');
        } catch (\Exception $e) {
            $this->error('✗ Administrative Application failed: ' . $e->getMessage());
        }
        
        // Test Motivation Letter
        $this->info('2. Testing Motivation Letter...');
        $data = [
            'applicant_name' => 'Marie Diallo',
            'applicant_address' => '456 Avenue de l\'Indépendance',
            'applicant_city' => 'Abidjan',
            'recipient_name' => 'Directeur des Ressources Humaines',
            'recipient_organization' => 'Société ABC',
            'position_title' => 'Développeur Web',
            'motivation_content' => 'Je suis passionné par le développement web et souhaite rejoindre votre équipe.',
            'date' => '2024-01-15'
        ];
        
        try {
            $result = $engine->renderTemplate('motivation_letter', $data, 'CI', 'fr');
            $this->info('✓ Motivation Letter template rendered successfully');
            $this->line('Sample output (first 200 chars): ' . substr($result, 0, 200) . '...');
        } catch (\Exception $e) {
            $this->error('✗ Motivation Letter failed: ' . $e->getMessage());
        }
        
        // Test Honor Attestation
        $this->info('3. Testing Honor Attestation...');
        $data = [
            'declarant_name' => 'Amadou Sow',
            'declarant_address' => '789 Rue de la République',
            'declaration_city' => 'Dakar',
            'birth_date' => '1990-05-15',
            'birth_place' => 'Thiès',
            'nationality' => 'sénégalaise',
            'identity_document_type' => 'Carte d\'identité nationale',
            'identity_document_number' => 'SN123456789',
            'declaration_content' => 'Je déclare sur l\'honneur n\'avoir jamais été condamné.',
            'date' => '2024-01-15'
        ];
        
        try {
            $result = $engine->renderTemplate('honor_attestation', $data, 'SN', 'fr');
            $this->info('✓ Honor Attestation template rendered successfully');
            $this->line('Sample output (first 200 chars): ' . substr($result, 0, 200) . '...');
        } catch (\Exception $e) {
            $this->error('✗ Honor Attestation failed: ' . $e->getMessage());
        }
        
        $this->info('Document types testing completed!');
        return 0;
    }
}