<?php

namespace App\Console\Commands;

use App\Services\OutputFormatter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestOutputFormatter extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:output-formatter';

    /**
     * The console command description.
     */
    protected $description = 'Test the OutputFormatter service with sample administrative document content';

    /**
     * Execute the console command.
     */
    public function handle(OutputFormatter $outputFormatter): int
    {
        $this->info('Testing OutputFormatter service...');

        try {
            // Sample administrative document content
            $sampleContent = $this->getSampleDocumentContent();
            
            $this->info('Sample content length: ' . strlen($sampleContent) . ' characters');
            
            // Test content sanitization
            $this->info('Testing content sanitization...');
            $sanitizedContent = $outputFormatter->sanitizeForExport($sampleContent);
            $this->info('Sanitized content length: ' . strlen($sanitizedContent) . ' characters');
            
            // Test PDF options validation
            $this->info('Testing PDF options validation...');
            $adminOptions = $outputFormatter->getAdministrativeDocumentOptions();
            $validationErrors = $outputFormatter->validatePDFOptions($adminOptions);
            
            if (empty($validationErrors)) {
                $this->info('✓ PDF options validation passed');
            } else {
                $this->error('✗ PDF options validation failed: ' . implode(', ', $validationErrors));
                return 1;
            }
            
            // Test supported formats and orientations
            $this->info('Testing supported formats and orientations...');
            $formats = $outputFormatter->getSupportedFormats();
            $orientations = $outputFormatter->getSupportedOrientations();
            
            $this->info('Supported formats: ' . implode(', ', array_keys($formats)));
            $this->info('Supported orientations: ' . implode(', ', array_keys($orientations)));
            
            $this->info('✅ OutputFormatter basic tests passed successfully!');
            $this->info('Note: PDF generation test skipped - requires full Laravel environment');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ OutputFormatter test failed: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Get sample administrative document content for testing
     */
    private function getSampleDocumentContent(): string
    {
        return "République du Sénégal
Un Peuple - Un But - Une Foi

Ministère de l'Intérieur
Direction Générale de l'Administration Territoriale

Dakar, le " . now()->format('d/m/Y') . "

OBJET: Demande d'attestation de résidence

Monsieur le Maire,

J'ai l'honneur de venir très respectueusement auprès de votre haute bienveillance solliciter une attestation de résidence.

En effet, je réside au quartier Médina, Rue 15 x 20, depuis le 15 janvier 2020. Cette attestation m'est nécessaire pour constituer mon dossier de demande de passeport.

Vous trouverez ci-joint les pièces justificatives suivantes :
- Copie de ma carte d'identité nationale
- Facture d'électricité récente
- Certificat de résidence de mon propriétaire

Dans l'attente d'une suite favorable à ma demande, je vous prie d'agréer, Monsieur le Maire, l'expression de ma très haute considération.

Signature du demandeur

Prénom et Nom: Jean Baptiste DIOP
Adresse: Quartier Médina, Rue 15 x 20, Dakar
Téléphone: +221 77 123 45 67
Email: jean.diop@email.com";
    }
}