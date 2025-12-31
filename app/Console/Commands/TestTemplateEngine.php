<?php

namespace App\Console\Commands;

use App\Services\TemplateEngine;
use Illuminate\Console\Command;

class TestTemplateEngine extends Command
{
    protected $signature = 'template:test';
    protected $description = 'Test the TemplateEngine functionality';

    public function handle(TemplateEngine $templateEngine): int
    {
        $this->info('Testing TemplateEngine functionality...');
        
        // Test data for administrative application
        $data = [
            'sender_name' => 'Jean Baptiste Ouédraogo',
            'sender_address' => '123 Avenue de l\'Indépendance',
            'sender_city' => 'Ouagadougou',
            'sender_phone' => '+226 70 12 34 56',
            'sender_email' => 'jean.ouedraogo@email.com',
            'recipient_name' => 'Monsieur le Directeur',
            'recipient_organization' => 'Direction Générale de la Police Nationale',
            'recipient_address' => 'Avenue Charles de Gaulle',
            'recipient_city' => 'Ouagadougou',
            'subject' => 'Demande de certificat de bonne vie et mœurs',
            'request_context' => 'Je sollicite par la présente l\'obtention d\'un certificat de bonne vie et mœurs.',
            'request_details' => 'Ce document m\'est nécessaire pour constituer un dossier de candidature à un emploi dans la fonction publique.',
            'justification' => 'Je n\'ai jamais fait l\'objet de poursuites judiciaires et jouis d\'une bonne réputation.',
            'expected_outcome' => 'J\'espère obtenir ce certificat dans les meilleurs délais.',
            'attachments' => 'Copie de la carte d\'identité, Timbre fiscal'
        ];

        try {
            // Test template rendering
            $this->info('Rendering administrative application template...');
            $result = $templateEngine->renderTemplate('administrative_application', $data, 'SN', 'fr');
            
            $this->line('Generated document:');
            $this->line(str_repeat('-', 80));
            $this->line($result);
            $this->line(str_repeat('-', 80));
            
            // Test validation
            $this->info('Testing validation...');
            $errors = $templateEngine->validateTemplateData('administrative_application', $data, 'SN', 'fr');
            
            if (empty($errors)) {
                $this->info('✓ Validation passed');
            } else {
                $this->error('✗ Validation failed:');
                foreach ($errors as $error) {
                    $this->error("  - {$error}");
                }
            }
            
            // Test supported document types
            $this->info('Supported document types:');
            $types = $templateEngine->getSupportedDocumentTypes();
            foreach ($types as $type) {
                $this->line("  - {$type}");
            }
            
            $this->info('TemplateEngine test completed successfully!');
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}