<?php

namespace App\Services;

use App\Data\DocumentGenerationRequest;
use App\Data\DocumentGenerationResult;
use App\Models\GeneratedDocument;
use App\Enums\Country;
use App\Enums\DocumentType;
use App\Enums\ToneLevel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Exception;

class DocumentGeneratorService
{
    public function __construct(
        private TemplateEngine $templateEngine,
        private CountryAdapter $countryAdapter,
    ) {}

    /**
     * Generate a complete administrative document
     * 
     * Requirements: 1.1, 1.2, 4.4, 4.5, 8.2
     */
    public function generateDocument(DocumentGenerationRequest $request): DocumentGenerationResult
    {
        try {
            // Validate request data integrity (Requirement 1.2, 8.2)
            $validationResult = $this->validateRequest($request);
            if (!$validationResult['valid']) {
                return DocumentGenerationResult::failure(
                    'Données de requête invalides',
                    $validationResult['errors'],
                    $request->session_id
                );
            }

            // Generate session ID if not provided (Requirement 8.2)
            $sessionId = $request->session_id ?? $this->generateSessionId();

            // Start database transaction for data integrity
            return DB::transaction(function () use ($request, $sessionId) {
                // Step 1: Render base template with user data (Requirement 1.1)
                $baseContent = $this->templateEngine->renderTemplate(
                    $request->document_type,
                    $request->fields,
                    $request->country,
                    $request->language
                );

                // Step 2: Apply country-specific adaptations (Requirement 4.4, 4.5)
                $adaptedContent = $this->countryAdapter->adaptForCountry(
                    $baseContent,
                    $request->country
                );

                // Step 3: Apply politeness formulas based on tone level (Requirement 4.4, 4.5)
                $finalContent = $this->countryAdapter->applyPolitenesFormulas(
                    $adaptedContent,
                    $request->country,
                    $request->tone_level
                );

                // Step 4: Final content sanitization and validation
                $sanitizedContent = $this->sanitizeContent($finalContent);
                
                // Validate content integrity (Requirement 1.2)
                if (!$this->validateContentIntegrity($request->fields, $sanitizedContent)) {
                    throw new Exception('Intégrité du contenu compromise lors de la génération');
                }

                // Step 5: Store in temporary session (no persistence beyond session - Requirement 8.2)
                $documentId = $this->storeTemporaryDocument(
                    $sessionId,
                    $request,
                    $sanitizedContent
                );

                // Step 6: Prepare metadata
                $metadata = $this->prepareGenerationMetadata($request);

                return DocumentGenerationResult::success(
                    $sanitizedContent,
                    $sessionId,
                    $documentId,
                    $metadata
                );
            });

        } catch (InvalidArgumentException $e) {
            Log::warning('Document generation validation error', [
                'error' => $e->getMessage(),
                'request' => $request->toArray()
            ]);

            return DocumentGenerationResult::failure(
                'Erreur de validation: ' . $e->getMessage(),
                [],
                $request->session_id
            );

        } catch (Exception $e) {
            Log::error('Document generation failed', [
                'error' => $e->getMessage(),
                'request' => $request->toArray()
            ]);

            return DocumentGenerationResult::failure(
                'Erreur lors de la génération du document',
                [],
                $request->session_id
            );
        }
    }

    /**
     * Generate document preview without storing
     */
    public function previewDocument(DocumentGenerationRequest $request): DocumentGenerationResult
    {
        try {
            // Validate request
            $validationResult = $this->validateRequest($request);
            if (!$validationResult['valid']) {
                return DocumentGenerationResult::failure(
                    'Données de requête invalides',
                    $validationResult['errors'],
                    $request->session_id
                );
            }

            // Generate preview content (same process but no storage)
            $baseContent = $this->templateEngine->renderTemplate(
                $request->document_type,
                $request->fields,
                $request->country,
                $request->language
            );

            $adaptedContent = $this->countryAdapter->adaptForCountry(
                $baseContent,
                $request->country
            );

            $finalContent = $this->countryAdapter->applyPolitenesFormulas(
                $adaptedContent,
                $request->country,
                $request->tone_level
            );

            $sanitizedContent = $this->sanitizeContent($finalContent);

            return DocumentGenerationResult::success(
                $sanitizedContent,
                $request->session_id ?? $this->generateSessionId(),
                null,
                ['preview' => true]
            );

        } catch (Exception $e) {
            Log::error('Document preview failed', [
                'error' => $e->getMessage(),
                'request' => $request->toArray()
            ]);

            return DocumentGenerationResult::failure(
                'Erreur lors de la prévisualisation du document',
                [],
                $request->session_id
            );
        }
    }

    /**
     * Get all supported document types
     */
    public function getSupportedTypes(): Collection
    {
        return collect(DocumentType::toArray())->map(function ($value, $key) {
            $enum = DocumentType::make($key);
            return [
                'key' => $key,
                'value' => $value,
                'label' => $enum->label,
                'required_fields' => \App\Http\Requests\DocumentRequest::getRequiredFieldsForDocumentType($key)
            ];
        });
    }

    /**
     * Get all supported countries
     */
    public function getSupportedCountries(): Collection
    {
        $supportedCountries = $this->countryAdapter->getSupportedCountries();
        
        return collect(Country::toArray())->map(function ($value, $key) use ($supportedCountries) {
            $enum = Country::make($key);
            return [
                'key' => $key,
                'value' => $value,
                'label' => $enum->label,
                'supported' => isset($supportedCountries[$value])
            ];
        })->filter(function ($country) {
            return $country['supported'];
        });
    }

    /**
     * Get supported tone levels
     */
    public function getSupportedToneLevels(): Collection
    {
        return collect(ToneLevel::toArray())->map(function ($value, $key) {
            $enum = ToneLevel::make($key);
            return [
                'key' => $key,
                'value' => $value,
                'label' => $enum->label
            ];
        });
    }

    /**
     * Validate document generation request
     */
    private function validateRequest(DocumentGenerationRequest $request): array
    {
        $errors = [];

        // Check if document type is supported
        if (!$this->templateEngine->isDocumentTypeSupported($request->document_type)) {
            $errors[] = "Type de document non supporté: {$request->document_type}";
        }

        // Check if country is supported
        if (!$this->countryAdapter->isCountrySupported($request->country)) {
            $errors[] = "Pays non supporté: {$request->country}";
        }

        // Check required fields
        if (!$request->hasAllRequiredFields()) {
            $missingFields = $request->getMissingRequiredFields();
            $errors[] = "Champs obligatoires manquants: " . implode(', ', $missingFields);
        }

        // Validate template exists
        $template = $this->templateEngine->getTemplate(
            $request->document_type,
            $request->country,
            $request->language
        );

        if (!$template) {
            $errors[] = "Template non trouvé pour: {$request->document_type}, {$request->country}, {$request->language}";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Validate content integrity - ensure user data is preserved
     * Requirement 1.2: No additions, modifications, or omissions of factual information
     */
    private function validateContentIntegrity(array $originalFields, string $generatedContent): bool
    {
        // Check that all provided field values appear in the generated content
        foreach ($originalFields as $field => $value) {
            if (!empty($value) && is_string($value)) {
                // Allow for minor formatting changes but ensure core content is preserved
                $normalizedValue = trim($value);
                $normalizedContent = preg_replace('/\s+/', ' ', $generatedContent);
                
                if (strpos($normalizedContent, $normalizedValue) === false) {
                    Log::warning('Content integrity violation detected', [
                        'field' => $field,
                        'original_value' => $value,
                        'content_length' => strlen($generatedContent)
                    ]);
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Sanitize content for final output
     */
    private function sanitizeContent(string $content): string
    {
        // Remove HTML tags and comments
        $content = strip_tags($content);
        
        // Remove HTML entities
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Normalize whitespace
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);
        $content = preg_replace('/[ \t]+/', ' ', $content);
        
        // Clean up line breaks
        $lines = explode("\n", $content);
        $lines = array_map('trim', $lines);
        
        // Remove empty lines at start and end
        while (!empty($lines) && $lines[0] === '') {
            array_shift($lines);
        }
        while (!empty($lines) && $lines[count($lines) - 1] === '') {
            array_pop($lines);
        }
        
        return implode("\n", $lines);
    }

    /**
     * Store document temporarily in session (Requirement 8.2)
     * No persistence beyond session - documents expire automatically
     */
    private function storeTemporaryDocument(
        string $sessionId,
        DocumentGenerationRequest $request,
        string $content
    ): string {
        $document = GeneratedDocument::create([
            'session_id' => $sessionId,
            'document_type' => $request->document_type,
            'country' => $request->country,
            'tone_level' => $request->tone_level,
            'language' => $request->language,
            'content' => $content,
            'metadata' => [
                'generated_at' => now()->toISOString(),
                'fields_count' => count($request->fields),
                'content_length' => strlen($content)
            ],
            'expires_at' => now()->addHours(24) // Expire after 24 hours
        ]);

        return (string) $document->id;
    }

    /**
     * Generate unique session ID
     */
    private function generateSessionId(): string
    {
        return GeneratedDocument::generateSessionId();
    }

    /**
     * Prepare generation metadata
     */
    private function prepareGenerationMetadata(DocumentGenerationRequest $request): array
    {
        return [
            'document_type' => $request->document_type,
            'country' => $request->country,
            'tone_level' => $request->tone_level,
            'language' => $request->language,
            'fields_provided' => count($request->fields),
            'generation_timestamp' => now()->toISOString(),
            'template_engine_version' => '1.0',
            'country_adapter_version' => '1.0'
        ];
    }

    /**
     * Clean up expired temporary documents
     */
    public function cleanupExpiredDocuments(): int
    {
        return GeneratedDocument::cleanupExpired();
    }

    /**
     * Get document by session and ID
     */
    public function getDocumentBySession(string $sessionId, string $documentId): ?GeneratedDocument
    {
        return GeneratedDocument::where('session_id', $sessionId)
            ->where('id', $documentId)
            ->notExpired()
            ->first();
    }
}