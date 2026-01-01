<?php

namespace App\Http\Controllers;

use App\Data\DocumentGenerationRequest;
use App\Data\DocumentGenerationResult;
use App\Http\Requests\DocumentRequest;
use App\Services\DocumentGeneratorService;
use App\Services\ErrorHandlingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    public function __construct(
        private DocumentGeneratorService $documentGenerator,
        private ErrorHandlingService $errorHandler
    ) {
        //
    }

    /**
     * Generate a document preview
     * 
     * @param DocumentRequest $request
     * @return JsonResponse
     */
    public function preview(DocumentRequest $request): JsonResponse
    {
        try {
            // Create document generation request from validated data
            $generationRequest = $this->createGenerationRequest($request);
            
            // Generate preview
            $result = $this->documentGenerator->previewDocument($generationRequest);
            
            if (!$result->isSuccessful()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'PREVIEW_GENERATION_FAILED',
                        'message' => $result->getFormattedErrorMessage(),
                        'timestamp' => now()->toISOString()
                    ]
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'content' => $result->content,
                    'metadata' => $result->metadata,
                    'session_id' => $result->session_id
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Document preview failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->safe()->toArray()
            ]);

            return $this->errorHandler->createJsonErrorResponse(
                'PREVIEW_ERROR',
                'Erreur lors de la génération de l\'aperçu',
                [],
                500
            );
        }
    }

    /**
     * Generate a complete document with optional async PDF generation
     * 
     * @param DocumentRequest $request
     * @return JsonResponse
     */
    public function store(DocumentRequest $request): JsonResponse
    {
        try {
            // Create document generation request from validated data
            $generationRequest = $this->createGenerationRequest($request);
            
            // Generate document
            $result = $this->documentGenerator->generateDocument($generationRequest);
            
            if (!$result->isSuccessful()) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'code' => 'DOCUMENT_GENERATION_FAILED',
                        'message' => $result->getFormattedErrorMessage(),
                        'timestamp' => now()->toISOString()
                    ]
                ], 400);
            }

            // Cache the result for quick access
            $cacheKey = "document:{$result->session_id}:{$result->document_id}";
            Cache::put($cacheKey, $result, now()->addHours(24));

            // Queue PDF generation for better performance
            if ($request->boolean('generate_pdf', true)) {
                \App\Jobs\GenerateDocumentPDF::dispatch(
                    $result->document_id,
                    $result->session_id,
                    $generationRequest,
                    $request->input('pdf_options', [])
                )->delay(now()->addSeconds(2)); // Small delay to ensure document is cached
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'content' => $result->content,
                    'document_id' => $result->document_id,
                    'session_id' => $result->session_id,
                    'metadata' => $result->metadata,
                    'download_url' => route('documents.download', [
                        'session' => $result->session_id,
                        'document' => $result->document_id
                    ]),
                    'pdf_status_url' => route('api.documents.pdf-status', [
                        'session' => $result->session_id,
                        'document' => $result->document_id
                    ])
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Document generation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'request_data' => $request->safe()->toArray()
            ]);

            return $this->errorHandler->createJsonErrorResponse(
                'GENERATION_ERROR',
                'Erreur lors de la génération du document',
                [],
                500
            );
        }
    }

    /**
     * Download a generated document as PDF
     * 
     * @param Request $request
     * @param string $session
     * @param string $document
     * @return BinaryFileResponse|JsonResponse
     */
    public function download(Request $request, string $session, string $document): BinaryFileResponse|JsonResponse
    {
        try {
            // Validate session ownership
            if ($session !== session()->getId()) {
                return $this->errorHandler->createJsonErrorResponse(
                    'UNAUTHORIZED_ACCESS',
                    'Accès non autorisé au document',
                    [],
                    403
                );
            }

            // Try to get from cache first
            $cacheKey = "document:{$session}:{$document}";
            $result = Cache::get($cacheKey);

            if (!$result) {
                // Fallback to database
                $documentRecord = $this->documentGenerator->getDocumentBySession($session, $document);
                
                if (!$documentRecord) {
                    return $this->errorHandler->createJsonErrorResponse(
                        'DOCUMENT_NOT_FOUND',
                        'Document non trouvé ou expiré',
                        [],
                        404
                    );
                }

                // Recreate result from database record
                $result = $documentRecord->toDocumentGenerationResult();
            }

            // Generate PDF
            $generationRequest = $this->createGenerationRequestFromResult($result);
            $pdfResult = $this->documentGenerator->generateDocumentPDF($generationRequest);

            if (!$pdfResult->isSuccessful()) {
                return $this->errorHandler->createJsonErrorResponse(
                    'PDF_GENERATION_FAILED',
                    'Erreur lors de la génération du PDF',
                    [],
                    500
                );
            }

            // Get PDF path from metadata
            $pdfPath = $pdfResult->metadata['pdf_path'] ?? null;
            
            if (!$pdfPath || !Storage::exists($pdfPath)) {
                return $this->errorHandler->createJsonErrorResponse(
                    'PDF_FILE_NOT_FOUND',
                    'Fichier PDF non trouvé',
                    [],
                    404
                );
            }

            // Generate filename
            $filename = $this->generateDownloadFilename($result);

            // Return file download response
            return Storage::download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Exception $e) {
            Log::error('Document download failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'session' => $session,
                'document' => $document
            ]);

            return $this->errorHandler->createJsonErrorResponse(
                'DOWNLOAD_ERROR',
                'Erreur lors du téléchargement',
                [],
                500
            );
        }
    }

    /**
     * Get supported document types
     * 
     * @return JsonResponse
     */
    public function types(): JsonResponse
    {
        try {
            $types = Cache::remember('document_types', 3600, function () {
                return $this->documentGenerator->getSupportedTypes();
            });

            return response()->json([
                'success' => true,
                'data' => $types
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get document types', ['error' => $e->getMessage()]);

            return $this->errorHandler->createJsonErrorResponse(
                'TYPES_ERROR',
                'Erreur lors de la récupération des types de documents',
                [],
                500
            );
        }
    }

    /**
     * Get supported countries
     * 
     * @return JsonResponse
     */
    public function countries(): JsonResponse
    {
        try {
            $countries = Cache::remember('supported_countries', 3600, function () {
                return $this->documentGenerator->getSupportedCountries();
            });

            return response()->json([
                'success' => true,
                'data' => $countries
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get supported countries', ['error' => $e->getMessage()]);

            return $this->errorHandler->createJsonErrorResponse(
                'COUNTRIES_ERROR',
                'Erreur lors de la récupération des pays supportés',
                [],
                500
            );
        }
    }

    /**
     * Get supported tone levels
     * 
     * @return JsonResponse
     */
    public function toneLevels(): JsonResponse
    {
        try {
            $toneLevels = Cache::remember('tone_levels', 3600, function () {
                return $this->documentGenerator->getSupportedToneLevels();
            });

            return response()->json([
                'success' => true,
                'data' => $toneLevels
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get tone levels', ['error' => $e->getMessage()]);

            return $this->errorHandler->createJsonErrorResponse(
                'TONE_LEVELS_ERROR',
                'Erreur lors de la récupération des niveaux de formalité',
                [],
                500
            );
        }
    }

    /**
     * Check PDF generation status
     * 
     * @param Request $request
     * @param string $session
     * @param string $document
     * @return JsonResponse
     */
    public function pdfStatus(Request $request, string $session, string $document): JsonResponse
    {
        try {
            // Validate session ownership
            if ($session !== session()->getId()) {
                return $this->errorHandler->createJsonErrorResponse(
                    'UNAUTHORIZED_ACCESS',
                    'Accès non autorisé au document',
                    [],
                    403
                );
            }

            // Check PDF status in cache
            $cacheKey = "pdf_ready:{$session}:{$document}";
            $pdfStatus = Cache::get($cacheKey);

            if (!$pdfStatus) {
                // Check if document exists
                $documentRecord = $this->documentGenerator->getDocumentBySession($session, $document);
                
                if (!$documentRecord) {
                    return $this->errorHandler->createJsonErrorResponse(
                        'DOCUMENT_NOT_FOUND',
                        'Document non trouvé ou expiré',
                        [],
                        404
                    );
                }

                // If document exists but no PDF status, assume it's still processing
                $pdfStatus = ['status' => 'processing'];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'pdf_status' => $pdfStatus['status'],
                    'generated_at' => $pdfStatus['generated_at'] ?? null,
                    'error' => $pdfStatus['error'] ?? null,
                    'download_ready' => $pdfStatus['status'] === 'ready'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('PDF status check failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
                'session' => $session,
                'document' => $document
            ]);

            return $this->errorHandler->createJsonErrorResponse(
                'PDF_STATUS_ERROR',
                'Erreur lors de la vérification du statut PDF',
                [],
                500
            );
        }
    }

    /**
     * Health check endpoint for monitoring
     * 
     * @return JsonResponse
     */
    public function health(): JsonResponse
    {
        try {
            // Check database connectivity
            $dbStatus = $this->checkDatabaseHealth();
            
            // Check cache connectivity
            $cacheStatus = $this->checkCacheHealth();
            
            // Check storage accessibility
            $storageStatus = $this->checkStorageHealth();

            $overallStatus = $dbStatus && $cacheStatus && $storageStatus;

            return response()->json([
                'success' => $overallStatus,
                'status' => $overallStatus ? 'healthy' : 'unhealthy',
                'checks' => [
                    'database' => $dbStatus ? 'ok' : 'error',
                    'cache' => $cacheStatus ? 'ok' : 'error',
                    'storage' => $storageStatus ? 'ok' : 'error'
                ],
                'timestamp' => now()->toISOString()
            ], $overallStatus ? 200 : 503);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'status' => 'error',
                'error' => 'Health check failed',
                'timestamp' => now()->toISOString()
            ], 503);
        }
    }

    /**
     * Create DocumentGenerationRequest from validated request
     */
    private function createGenerationRequest(DocumentRequest $request): DocumentGenerationRequest
    {
        return new DocumentGenerationRequest(
            country: $request->validated('country'),
            document_type: $request->validated('document_type'),
            tone_level: $request->validated('tone_level'),
            language: $request->validated('language', 'fr'),
            fields: $request->validated('fields', []),
            session_id: session()->getId()
        );
    }

    /**
     * Create DocumentGenerationRequest from existing result
     */
    private function createGenerationRequestFromResult($result): DocumentGenerationRequest
    {
        return new DocumentGenerationRequest(
            country: $result->metadata['country'],
            document_type: $result->metadata['document_type'],
            tone_level: $result->metadata['tone_level'],
            language: $result->metadata['language'],
            fields: [], // Fields not needed for PDF generation
            session_id: $result->session_id
        );
    }

    /**
     * Generate download filename
     */
    private function generateDownloadFilename($result): string
    {
        $documentType = str_replace('_', '-', $result->metadata['document_type'] ?? 'document');
        $country = strtolower($result->metadata['country'] ?? 'document');
        $timestamp = now()->format('Y-m-d_H-i-s');
        
        return "{$documentType}_{$country}_{$timestamp}.pdf";
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth(): bool
    {
        try {
            \DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check cache health
     */
    private function checkCacheHealth(): bool
    {
        try {
            Cache::put('health_check', 'ok', 10);
            return Cache::get('health_check') === 'ok';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check storage health
     */
    private function checkStorageHealth(): bool
    {
        try {
            Storage::put('health_check.txt', 'ok');
            $result = Storage::get('health_check.txt') === 'ok';
            Storage::delete('health_check.txt');
            return $result;
        } catch (\Exception $e) {
            return false;
        }
    }
}