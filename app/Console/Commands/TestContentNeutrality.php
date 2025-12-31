<?php

namespace App\Console\Commands;

use App\Services\ContentNeutralityValidator;
use Illuminate\Console\Command;

class TestContentNeutrality extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:content-neutrality {--content= : Content to test}';

    /**
     * The console command description.
     */
    protected $description = 'Test content neutrality validation with sample content';

    /**
     * Execute the console command.
     */
    public function handle(ContentNeutralityValidator $validator): int
    {
        $this->info('Testing Content Neutrality Validator');
        $this->info('=====================================');

        // Test content from command line or use samples
        $testContent = $this->option('content');
        
        if ($testContent) {
            $this->testSingleContent($validator, $testContent);
        } else {
            $this->testSampleContents($validator);
        }

        return 0;
    }

    private function testSingleContent(ContentNeutralityValidator $validator, string $content): void
    {
        $this->info("Testing content: " . substr($content, 0, 100) . "...");
        
        $result = $validator->validateContentNeutrality($content);
        
        if ($result['is_neutral']) {
            $this->info('✅ Content is neutral');
        } else {
            $this->error('❌ Content neutrality violations detected:');
            $this->displayViolations($result['violations']);
        }
    }

    private function testSampleContents(ContentNeutralityValidator $validator): void
    {
        $testCases = [
            // Neutral content (should pass)
            [
                'name' => 'Neutral Administrative Request',
                'content' => 'Je soussigné Jean Dupont, demeurant à Dakar, sollicite par la présente une attestation de résidence. Cette demande est motivée par les démarches administratives en cours.',
                'should_pass' => true
            ],
            
            // Legal advice violation (should fail)
            [
                'name' => 'Legal Advice Violation',
                'content' => 'Selon la loi, vous devriez consulter un avocat avant de procéder. Il est obligatoire de respecter l\'article 15 du code civil.',
                'should_pass' => false
            ],
            
            // Opinion violation (should fail)
            [
                'name' => 'Opinion Violation',
                'content' => 'Je pense que cette décision est excellente et probablement la meilleure solution possible.',
                'should_pass' => false
            ],
            
            // Personal language violation (should fail)
            [
                'name' => 'Personal Language Violation',
                'content' => 'Personnellement, je ressens que cette situation est malheureusement très compliquée.',
                'should_pass' => false
            ],
            
            // Legal reference violation (should fail)
            [
                'name' => 'Legal Reference Violation',
                'content' => 'Conformément à l\'article 25 du code civil et selon la jurisprudence de la Cour de cassation.',
                'should_pass' => false
            ],
            
            // Content modification violation (should fail)
            [
                'name' => 'Content Modification Violation',
                'content' => 'Votre demande cela signifie que vous devez donc procéder autrement dit suivre la procédure.',
                'should_pass' => false
            ]
        ];

        foreach ($testCases as $testCase) {
            $this->info("\nTesting: " . $testCase['name']);
            $this->line("Content: " . substr($testCase['content'], 0, 80) . "...");
            
            $result = $validator->validateContentNeutrality($testCase['content']);
            
            $passed = $result['is_neutral'];
            $expected = $testCase['should_pass'];
            
            if ($passed === $expected) {
                $status = $passed ? '✅ PASS (Neutral)' : '✅ PASS (Violations detected)';
                $this->info($status);
            } else {
                $status = $passed ? '❌ FAIL (Should have violations)' : '❌ FAIL (Should be neutral)';
                $this->error($status);
            }
            
            if (!$result['is_neutral']) {
                $this->displayViolations($result['violations']);
            }
        }

        // Test user content preservation
        $this->info("\n" . str_repeat('=', 50));
        $this->info('Testing User Content Preservation');
        $this->info(str_repeat('=', 50));
        
        $originalFields = [
            'applicant_name' => 'Jean Dupont',
            'subject' => 'Demande d\'attestation',
            'request_details' => 'Je sollicite une attestation de résidence pour mes démarches administratives.'
        ];
        
        $generatedContent = 'Monsieur Jean Dupont demande une attestation. Il sollicite une attestation de résidence pour ses démarches administratives. Objet: Demande d\'attestation.';
        
        $this->info("Testing content preservation...");
        $result = $validator->validateContentNeutrality($generatedContent, $originalFields);
        
        if ($result['is_neutral']) {
            $this->info('✅ Content preservation validated');
        } else {
            $this->error('❌ Content preservation issues detected');
            $this->displayViolations($result['violations']);
        }
    }

    private function displayViolations(array $violations): void
    {
        foreach ($violations as $type => $violationList) {
            $this->warn("  {$type}: " . count($violationList) . " violation(s)");
            
            foreach ($violationList as $violation) {
                if (isset($violation['matched_text'])) {
                    $this->line("    - Matched: '{$violation['matched_text']}'");
                    $this->line("      Context: " . substr($violation['context'], 0, 100) . "...");
                } elseif (isset($violation['field'])) {
                    $this->line("    - Field: {$violation['field']}");
                    $this->line("      Missing words: " . implode(', ', $violation['missing_words']));
                }
            }
        }
    }
}