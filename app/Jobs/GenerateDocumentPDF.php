<?php

namespace App\Jobs;

use App\Data\DocumentGenerationRequest;
use App\Services\DocumentGeneratorService;
use App\Models\GeneratedDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GenerateDocumentPDF implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 2;
    public int $timeout = 120; // 2 minutes timeout

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $documentId,
        public string $sessionId,
        public DocumentGenerationRequest $request,
        public array $pdfOptions = []
    ) {
        // Set queue priority based on user type or request urgency
        $this->onQueue('pdf-generation');
    }

    /**
     * Execute the job.
     */
    public function handle(DocumentGeneratorService $documentGenerator): void
    {
        try {
            Log::info('Starting PDF generation job', [
                'document_id' => $this->documentId,
                'session_id' => $this->sessionId,
                'queue' => $this->queue
            ]);

            // Generate PDF
            $result = $documentGenerator->generateDocumentPDF($this->request, $this->pdfOptions);

            if ($result->isSuccessful()) {
                // Update document record with PDF path
                $document = GeneratedDocument::find($this->documentId);
                if ($document) {
                    $document->update([
                        'pdf_path' => $result->metadata['pdf_path'] ?? null,
                        'metadata' => array_merge($document->metadata ?? [], [
                            'pdf_generated_at' => now()->toISOString(),
                            'pdf_size' => $result->metadata['pdf_size'] ?? null,
                            'pdf_generation_time' => microtime(true) - LARAVEL_START
                        ])
                    ]);
                }

                // Cache the result for quick access
                $cacheKey = "pdf_ready:{$this->sessionId}:{$this->documentId}";
                Cache::put($cacheKey, [
                    'status' => 'ready',
                    'pdf_path' => $result->metadata['pdf_path'] ?? null,
                    'generated_at' => now()->toISOString()
                ], now()->addHours(24));

                Log::info('PDF generation job completed successfully', [
                    'document_id' => $this->documentId,
                    'session_id' => $this->sessionId,
                    'pdf_path' => $result->metadata['pdf_path'] ?? null
                ]);

            } else {
                throw new \Exception('PDF generation failed: ' . $result->getFormattedErrorMessage());
            }

        } catch (\Exception $e) {
            Log::error('PDF generation job failed', [
                'document_id' => $this->documentId,
                'session_id' => $this->sessionId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts()
            ]);

            // Cache failure status
            $cacheKey = "pdf_ready:{$this->sessionId}:{$this->documentId}";
            Cache::put($cacheKey, [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'failed_at' => now()->toISOString()
            ], now()->addHours(1));

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('PDF generation job failed permanently', [
            'document_id' => $this->documentId,
            'session_id' => $this->sessionId,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts()
        ]);

        // Update cache with permanent failure status
        $cacheKey = "pdf_ready:{$this->sessionId}:{$this->documentId}";
        Cache::put($cacheKey, [
            'status' => 'permanently_failed',
            'error' => $exception->getMessage(),
            'failed_at' => now()->toISOString()
        ], now()->addHours(24));

        // Optionally notify user or admin about the failure
        // This could be extended to send notifications
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'pdf-generation',
            "session:{$this->sessionId}",
            "document:{$this->documentId}"
        ];
    }
}