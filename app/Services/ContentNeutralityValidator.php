<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * Content Neutrality Validator Service
 * 
 * Ensures generated documents maintain strict neutrality by detecting and preventing:
 * - Legal advice or law interpretation (Requirement 6.1)
 * - Opinions or subjective judgments (Requirement 6.2)
 * - Non-neutral or personal language (Requirement 6.3)
 * - Legal references or citations (Requirement 6.4)
 * - Content modifications beyond formatting (Requirement 6.5)
 */
class ContentNeutralityValidator
{
    /**
     * Patterns that indicate legal advice or law interpretation
     * Requirements: 6.1
     */
    private array $legalAdvicePatterns = [
        // French patterns
        '/\b(vous devriez|il faut|il est obligatoire de|la loi exige|selon la loi|conformément à l\'article)\b/iu',
        '/\b(conseil juridique|avis juridique|interprétation légale|selon le droit)\b/iu',
        '/\b(il est recommandé de|vous êtes tenu de|vous avez l\'obligation de)\b/iu',
        '/\b(en vertu de la loi|aux termes de la loi|la jurisprudence établit)\b/iu',
        
        // English patterns
        '/\b(you should|you must|it is mandatory|the law requires|according to law|pursuant to article)\b/iu',
        '/\b(legal advice|legal opinion|legal interpretation|under the law)\b/iu',
        '/\b(it is recommended that|you are required to|you have the obligation to)\b/iu',
        '/\b(under the law|pursuant to law|case law establishes)\b/iu',
    ];

    /**
     * Patterns that indicate opinions or subjective judgments
     * Requirements: 6.2
     */
    private array $opinionPatterns = [
        // French patterns
        '/\b(je pense que|je crois que|à mon avis|selon moi|il me semble)\b/iu',
        '/\b(probablement|vraisemblablement|il est possible que|il se peut que)\b/iu',
        '/\b(excellent|parfait|idéal|optimal|meilleur|pire|terrible)\b/iu',
        '/\b(je recommande|je suggère|je propose|il vaudrait mieux)\b/iu',
        
        // English patterns
        '/\b(I think|I believe|in my opinion|according to me|it seems to me)\b/iu',
        '/\b(probably|likely|it is possible that|it may be that)\b/iu',
        '/\b(excellent|perfect|ideal|optimal|best|worst|terrible)\b/iu',
        '/\b(I recommend|I suggest|I propose|it would be better)\b/iu',
    ];

    /**
     * Patterns that indicate non-neutral or personal language
     * Requirements: 6.3
     */
    private array $personalLanguagePatterns = [
        // French patterns
        '/\b(mon|ma|mes|notre|nos|votre|vos|leur|leurs)\s+(opinion|point de vue|sentiment)\b/iu',
        '/\b(personnellement|subjectivement|émotionnellement)\b/iu',
        '/\b(je ressens|je sens que|j\'ai l\'impression)\b/iu',
        '/\b(malheureusement|heureusement|tristement|joyeusement)\b/iu',
        
        // English patterns
        '/\b(my|our|your|their)\s+(opinion|viewpoint|feeling)\b/iu',
        '/\b(personally|subjectively|emotionally)\b/iu',
        '/\b(I feel|I sense that|I have the impression)\b/iu',
        '/\b(unfortunately|fortunately|sadly|joyfully)\b/iu',
    ];

    /**
     * Patterns that indicate legal references or citations
     * Requirements: 6.4
     */
    private array $legalReferencePatterns = [
        // French patterns
        '/\b(article\s+\d+|loi\s+n°|décret\s+n°|arrêté\s+n°)\b/iu',
        '/\b(code civil|code pénal|code du travail|constitution)\b/iu',
        '/\b(jurisprudence|précédent|arrêt|jugement)\s+(n°|du|de)\b/iu',
        '/\b(tribunal|cour|conseil d\'état|cour de cassation)\b/iu',
        
        // English patterns
        '/\b(article\s+\d+|law\s+no|decree\s+no|order\s+no)\b/iu',
        '/\b(civil code|penal code|labor code|constitution)\b/iu',
        '/\b(case law|precedent|ruling|judgment)\s+(no|of|from)\b/iu',
        '/\b(court|tribunal|supreme court|constitutional court)\b/iu',
    ];

    /**
     * Patterns that indicate content modification beyond formatting
     * Requirements: 6.5
     */
    private array $contentModificationPatterns = [
        // Patterns indicating added interpretative content
        '/\b(cela signifie que|ceci implique que|par conséquent|donc)\b/iu',
        '/\b(this means that|this implies that|therefore|consequently)\b/iu',
        '/\b(en d\'autres termes|autrement dit|c\'est-à-dire)\b/iu',
        '/\b(in other words|that is to say|namely)\b/iu',
    ];

    /**
     * Validate content for neutrality violations
     * 
     * @param string $content The content to validate
     * @param array $originalFields The original user-provided fields
     * @return array Validation result with violations if any
     */
    public function validateContentNeutrality(string $content, array $originalFields = []): array
    {
        $violations = [];

        // Check for legal advice patterns (Requirement 6.1)
        $legalAdviceViolations = $this->detectPatternViolations(
            $content, 
            $this->legalAdvicePatterns, 
            'legal_advice'
        );
        if (!empty($legalAdviceViolations)) {
            $violations['legal_advice'] = $legalAdviceViolations;
        }

        // Check for opinion patterns (Requirement 6.2)
        $opinionViolations = $this->detectPatternViolations(
            $content, 
            $this->opinionPatterns, 
            'opinions'
        );
        if (!empty($opinionViolations)) {
            $violations['opinions'] = $opinionViolations;
        }

        // Check for personal language patterns (Requirement 6.3)
        $personalLanguageViolations = $this->detectPatternViolations(
            $content, 
            $this->personalLanguagePatterns, 
            'personal_language'
        );
        if (!empty($personalLanguageViolations)) {
            $violations['personal_language'] = $personalLanguageViolations;
        }

        // Check for legal reference patterns (Requirement 6.4)
        $legalReferenceViolations = $this->detectPatternViolations(
            $content, 
            $this->legalReferencePatterns, 
            'legal_references'
        );
        if (!empty($legalReferenceViolations)) {
            $violations['legal_references'] = $legalReferenceViolations;
        }

        // Check for content modification patterns (Requirement 6.5)
        $modificationViolations = $this->detectPatternViolations(
            $content, 
            $this->contentModificationPatterns, 
            'content_modification'
        );
        if (!empty($modificationViolations)) {
            $violations['content_modification'] = $modificationViolations;
        }

        // Additional check: ensure user content wasn't modified beyond formatting
        if (!empty($originalFields)) {
            $contentModificationCheck = $this->validateUserContentPreservation($content, $originalFields);
            if (!$contentModificationCheck['valid']) {
                $violations['user_content_modification'] = $contentModificationCheck['violations'];
            }
        }

        // Log violations for monitoring (only if Log facade is available)
        if (!empty($violations) && class_exists('\Illuminate\Support\Facades\Log')) {
            try {
                Log::warning('Content neutrality violations detected', [
                    'violation_types' => array_keys($violations),
                    'violation_count' => array_sum(array_map('count', $violations)),
                    'content_length' => strlen($content)
                ]);
            } catch (\Exception $e) {
                // Silently ignore logging errors in tests
            }
        }

        return [
            'is_neutral' => empty($violations),
            'violations' => $violations,
            'violation_count' => array_sum(array_map('count', $violations))
        ];
    }

    /**
     * Detect pattern violations in content
     * 
     * @param string $content Content to check
     * @param array $patterns Regex patterns to match
     * @param string $violationType Type of violation for logging
     * @return array Found violations with context
     */
    private function detectPatternViolations(string $content, array $patterns, string $violationType): array
    {
        $violations = [];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $match) {
                    $matchText = $match[0];
                    $matchOffset = $match[1];
                    
                    // Get context around the match (50 characters before and after)
                    $contextStart = max(0, $matchOffset - 50);
                    $contextLength = min(strlen($content) - $contextStart, 150);
                    $context = substr($content, $contextStart, $contextLength);
                    
                    $violations[] = [
                        'type' => $violationType,
                        'matched_text' => $matchText,
                        'context' => $context,
                        'position' => $matchOffset,
                        'pattern' => $pattern
                    ];
                }
            }
        }

        return $violations;
    }

    /**
     * Validate that user-provided content is preserved without modification
     * Requirements: 6.5, 1.2
     * 
     * @param string $generatedContent The generated document content
     * @param array $originalFields Original user-provided fields
     * @return array Validation result
     */
    private function validateUserContentPreservation(string $generatedContent, array $originalFields): array
    {
        $violations = [];

        foreach ($originalFields as $fieldName => $fieldValue) {
            if (!empty($fieldValue) && is_string($fieldValue)) {
                // Normalize both strings for comparison (remove extra whitespace)
                $normalizedOriginal = preg_replace('/\s+/', ' ', trim($fieldValue));
                $normalizedGenerated = preg_replace('/\s+/', ' ', $generatedContent);

                // Check if the original content appears in the generated content
                if (strpos($normalizedGenerated, $normalizedOriginal) === false) {
                    // Check for partial matches to detect modifications
                    $words = explode(' ', $normalizedOriginal);
                    $missingWords = [];
                    
                    foreach ($words as $word) {
                        if (strlen($word) > 2 && strpos($normalizedGenerated, $word) === false) {
                            $missingWords[] = $word;
                        }
                    }

                    if (!empty($missingWords)) {
                        $violations[] = [
                            'field' => $fieldName,
                            'original_value' => $fieldValue,
                            'missing_words' => $missingWords,
                            'violation_type' => 'user_content_modified'
                        ];
                    }
                }
            }
        }

        return [
            'valid' => empty($violations),
            'violations' => $violations
        ];
    }

    /**
     * Get human-readable error messages for violations
     * 
     * @param array $violations Violations from validateContentNeutrality
     * @param string $language Language for error messages (fr|en)
     * @return array Human-readable error messages
     */
    public function getViolationMessages(array $violations, string $language = 'fr'): array
    {
        $messages = [];

        $messageTemplates = [
            'fr' => [
                'legal_advice' => 'Le contenu contient des conseils juridiques ou des interprétations de loi non autorisés.',
                'opinions' => 'Le contenu contient des opinions ou des jugements subjectifs non autorisés.',
                'personal_language' => 'Le contenu utilise un langage personnel ou non-neutre non autorisé.',
                'legal_references' => 'Le contenu contient des références légales ou des citations non autorisées.',
                'content_modification' => 'Le contenu a été modifié au-delà du formatage autorisé.',
                'user_content_modification' => 'Le contenu fourni par l\'utilisateur a été modifié de manière non autorisée.'
            ],
            'en' => [
                'legal_advice' => 'Content contains unauthorized legal advice or law interpretation.',
                'opinions' => 'Content contains unauthorized opinions or subjective judgments.',
                'personal_language' => 'Content uses unauthorized personal or non-neutral language.',
                'legal_references' => 'Content contains unauthorized legal references or citations.',
                'content_modification' => 'Content has been modified beyond authorized formatting.',
                'user_content_modification' => 'User-provided content has been modified in an unauthorized manner.'
            ]
        ];

        $templates = $messageTemplates[$language] ?? $messageTemplates['fr'];

        foreach ($violations as $violationType => $violationList) {
            if (isset($templates[$violationType])) {
                $messages[] = [
                    'type' => $violationType,
                    'message' => $templates[$violationType],
                    'count' => count($violationList),
                    'details' => $violationList
                ];
            }
        }

        return $messages;
    }

    /**
     * Check if content passes all neutrality requirements
     * 
     * @param string $content Content to validate
     * @param array $originalFields Original user fields for preservation check
     * @return bool True if content is neutral, false otherwise
     */
    public function isContentNeutral(string $content, array $originalFields = []): bool
    {
        $result = $this->validateContentNeutrality($content, $originalFields);
        return $result['is_neutral'];
    }

    /**
     * Sanitize content to remove potential neutrality violations
     * Note: This method only removes obvious violations and logs them.
     * It does not attempt to "fix" content as that would violate neutrality.
     * 
     * @param string $content Content to sanitize
     * @return array Sanitization result
     */
    public function sanitizeContent(string $content): array
    {
        $originalContent = $content;
        $removedViolations = [];

        // This method intentionally does minimal sanitization to avoid
        // modifying user content beyond what's absolutely necessary
        
        // Only remove obvious HTML/markup that might have been accidentally included
        $content = strip_tags($content);
        
        // Remove HTML entities
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Normalize whitespace without changing meaning
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);

        // Check if any content was actually modified
        $wasModified = $originalContent !== $content;

        if ($wasModified && class_exists('\Illuminate\Support\Facades\Log')) {
            try {
                Log::info('Content sanitization performed', [
                    'original_length' => strlen($originalContent),
                    'sanitized_length' => strlen($content),
                    'modifications' => 'HTML tags and entities removed, whitespace normalized'
                ]);
            } catch (\Exception $e) {
                // Silently ignore logging errors in tests
            }
        }

        return [
            'content' => $content,
            'was_modified' => $wasModified,
            'removed_violations' => $removedViolations,
            'original_length' => strlen($originalContent),
            'final_length' => strlen($content)
        ];
    }
}