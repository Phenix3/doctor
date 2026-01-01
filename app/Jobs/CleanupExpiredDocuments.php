<?php

namespace App\Jobs;

use App\Models\GeneratedDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class CleanupExpiredDocuments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300; // 5 minutes timeout

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->onQueue('cleanup');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting expired documents cleanup job');

            $startTime = microtime(true);
            $documentsDeleted = 0;
            $filesDeleted = 0;
            $cacheKeysDeleted = 0;

            // Get expired documents
            $expiredDocuments = GeneratedDocument::expired()->get();

            foreach ($expiredDocuments as $document) {
                try {
                    // Delete associated PDF file if exists
                    if ($document->pdf_path && Storage::exists($document->pdf_path)) {
                        Storage::delete($document->pdf_path);
                        $filesDeleted++;
                        
                        Log::debug('Deleted PDF file', [
                            'document_id' => $document->id,
                            'pdf_path' => $document->pdf_path
                        ]);
                    }

                    // Clear related cache entries
                    $cacheKeys = [
                        "document:{$document->session_id}:{$document->id}",
                        "pdf_ready:{$document->session_id}:{$document->id}"
                    ];

                    foreach ($cacheKeys as $key) {
                        if (Cache::has($key)) {
                            Cache::forget($key);
                            $cacheKeysDeleted++;
                        }
                    }

                    // Delete the document record
                    $document->delete();
                    $documentsDeleted++;

                } catch (\Exception $e) {
                    Log::warning('Failed to cleanup individual document', [
                        'document_id' => $document->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Clean up orphaned PDF files (files older than 48 hours without database record)
            $this->cleanupOrphanedFiles();

            // Clean up empty directories
            $this->cleanupEmptyDirectories();

            $executionTime = microtime(true) - $startTime;

            Log::info('Expired documents cleanup job completed', [
                'documents_deleted' => $documentsDeleted,
                'files_deleted' => $filesDeleted,
                'cache_keys_deleted' => $cacheKeysDeleted,
                'execution_time_seconds' => round($executionTime, 2)
            ]);

        } catch (\Exception $e) {
            Log::error('Expired documents cleanup job failed', [
                'error' => $e->getMessage()
            ]);

            throw $e;
        }
    }

    /**
     * Clean up orphaned PDF files
     */
    private function cleanupOrphanedFiles(): void
    {
        try {
            $documentsPath = 'documents';
            
            if (!Storage::exists($documentsPath)) {
                return;
            }

            $allFiles = Storage::allFiles($documentsPath);
            $orphanedFiles = 0;

            foreach ($allFiles as $file) {
                // Skip non-PDF files
                if (!str_ends_with($file, '.pdf')) {
                    continue;
                }

                // Check if file is older than 48 hours
                $fileTime = Storage::lastModified($file);
                if ($fileTime > (time() - 48 * 3600)) {
                    continue;
                }

                // Check if there's a database record for this file
                $hasRecord = GeneratedDocument::where('pdf_path', $file)->exists();
                
                if (!$hasRecord) {
                    Storage::delete($file);
                    $orphanedFiles++;
                    
                    Log::debug('Deleted orphaned PDF file', ['file' => $file]);
                }
            }

            if ($orphanedFiles > 0) {
                Log::info('Cleaned up orphaned PDF files', ['count' => $orphanedFiles]);
            }

        } catch (\Exception $e) {
            Log::warning('Failed to cleanup orphaned files', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Clean up empty directories
     */
    private function cleanupEmptyDirectories(): void
    {
        try {
            $documentsPath = 'documents';
            
            if (!Storage::exists($documentsPath)) {
                return;
            }

            $directories = Storage::allDirectories($documentsPath);
            $deletedDirectories = 0;

            // Sort directories by depth (deepest first) to clean up properly
            usort($directories, function ($a, $b) {
                return substr_count($b, '/') - substr_count($a, '/');
            });

            foreach ($directories as $directory) {
                $files = Storage::files($directory);
                $subdirectories = Storage::directories($directory);

                // If directory is empty (no files and no subdirectories)
                if (empty($files) && empty($subdirectories)) {
                    Storage::deleteDirectory($directory);
                    $deletedDirectories++;
                    
                    Log::debug('Deleted empty directory', ['directory' => $directory]);
                }
            }

            if ($deletedDirectories > 0) {
                Log::info('Cleaned up empty directories', ['count' => $deletedDirectories]);
            }

        } catch (\Exception $e) {
            Log::warning('Failed to cleanup empty directories', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Cleanup job failed permanently', [
            'error' => $exception->getMessage()
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return ['cleanup', 'maintenance'];
    }
}