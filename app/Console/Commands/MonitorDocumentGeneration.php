<?php

namespace App\Console\Commands;

use App\Models\GeneratedDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class MonitorDocumentGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:monitor 
                            {--format=table : Output format (table, json)}
                            {--detailed : Show detailed statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor document generation system performance and health';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $format = $this->option('format');
        $detailed = $this->option('detailed');

        $this->info('Document Generation System Monitor');
        $this->info('=====================================');

        $stats = $this->gatherStatistics($detailed);

        if ($format === 'json') {
            $this->line(json_encode($stats, JSON_PRETTY_PRINT));
        } else {
            $this->displayTableFormat($stats, $detailed);
        }

        // Check for any critical issues
        $issues = $this->checkForIssues($stats);
        if (!empty($issues)) {
            $this->warn('Critical Issues Detected:');
            foreach ($issues as $issue) {
                $this->error("⚠️  {$issue}");
            }
            return self::FAILURE;
        }

        $this->info('✅ System is healthy');
        return self::SUCCESS;
    }

    /**
     * Gather system statistics
     */
    private function gatherStatistics(bool $detailed): array
    {
        $stats = [
            'timestamp' => now()->toISOString(),
            'documents' => $this->getDocumentStats(),
            'storage' => $this->getStorageStats(),
            'cache' => $this->getCacheStats(),
            'queue' => $this->getQueueStats(),
            'database' => $this->getDatabaseStats(),
        ];

        if ($detailed) {
            $stats['performance'] = $this->getPerformanceStats();
            $stats['errors'] = $this->getErrorStats();
        }

        return $stats;
    }

    /**
     * Get document statistics
     */
    private function getDocumentStats(): array
    {
        return [
            'total_documents' => GeneratedDocument::count(),
            'active_documents' => GeneratedDocument::notExpired()->count(),
            'expired_documents' => GeneratedDocument::expired()->count(),
            'documents_with_pdf' => GeneratedDocument::whereNotNull('pdf_path')->count(),
            'documents_last_24h' => GeneratedDocument::where('created_at', '>=', now()->subDay())->count(),
            'documents_last_hour' => GeneratedDocument::where('created_at', '>=', now()->subHour())->count(),
            'average_content_length' => (int) GeneratedDocument::avg(DB::raw('LENGTH(content)')),
        ];
    }

    /**
     * Get storage statistics
     */
    private function getStorageStats(): array
    {
        $documentsPath = 'documents';
        $totalSize = 0;
        $fileCount = 0;

        if (Storage::exists($documentsPath)) {
            $files = Storage::allFiles($documentsPath);
            $fileCount = count($files);
            
            foreach ($files as $file) {
                $totalSize += Storage::size($file);
            }
        }

        return [
            'total_files' => $fileCount,
            'total_size_bytes' => $totalSize,
            'total_size_mb' => round($totalSize / 1024 / 1024, 2),
            'disk_free_space_gb' => round(disk_free_space(storage_path()) / 1024 / 1024 / 1024, 2),
            'disk_total_space_gb' => round(disk_total_space(storage_path()) / 1024 / 1024 / 1024, 2),
        ];
    }

    /**
     * Get cache statistics
     */
    private function getCacheStats(): array
    {
        try {
            // Test cache connectivity
            $testKey = 'monitor_test_' . time();
            Cache::put($testKey, 'test', 10);
            $cacheWorking = Cache::get($testKey) === 'test';
            Cache::forget($testKey);

            return [
                'cache_working' => $cacheWorking,
                'cache_driver' => config('cache.default'),
                'template_cache_keys' => $this->countCacheKeys('document_template_*'),
                'country_cache_keys' => $this->countCacheKeys('country_rules:*'),
                'document_cache_keys' => $this->countCacheKeys('document:*'),
            ];
        } catch (\Exception $e) {
            return [
                'cache_working' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get queue statistics
     */
    private function getQueueStats(): array
    {
        try {
            return [
                'default_queue_size' => Queue::size('default'),
                'pdf_generation_queue_size' => Queue::size('pdf-generation'),
                'cleanup_queue_size' => Queue::size('cleanup'),
                'failed_jobs' => DB::table('failed_jobs')->count(),
                'recent_failed_jobs' => DB::table('failed_jobs')
                    ->where('failed_at', '>=', now()->subHour())
                    ->count(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get database statistics
     */
    private function getDatabaseStats(): array
    {
        try {
            $connectionTime = microtime(true);
            DB::connection()->getPdo();
            $connectionTime = microtime(true) - $connectionTime;

            return [
                'connection_working' => true,
                'connection_time_ms' => round($connectionTime * 1000, 2),
                'document_templates_count' => DB::table('document_templates')->count(),
                'country_rules_count' => DB::table('country_rules')->count(),
                'users_count' => DB::table('users')->count(),
            ];
        } catch (\Exception $e) {
            return [
                'connection_working' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get performance statistics
     */
    private function getPerformanceStats(): array
    {
        return [
            'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'average_generation_time' => $this->getAverageGenerationTime(),
        ];
    }

    /**
     * Get error statistics
     */
    private function getErrorStats(): array
    {
        return [
            'recent_errors' => DB::table('failed_jobs')
                ->where('failed_at', '>=', now()->subDay())
                ->select('queue', 'exception')
                ->get()
                ->groupBy('queue')
                ->map(function ($jobs) {
                    return $jobs->count();
                })
                ->toArray(),
        ];
    }

    /**
     * Count cache keys matching pattern
     */
    private function countCacheKeys(string $pattern): int
    {
        try {
            if (config('cache.default') === 'redis') {
                return count(Cache::getRedis()->keys($pattern));
            }
            return 0; // Can't count keys for other cache drivers easily
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get average generation time from metadata
     */
    private function getAverageGenerationTime(): ?float
    {
        $documents = GeneratedDocument::whereNotNull('metadata')
            ->where('created_at', '>=', now()->subDay())
            ->get();

        if ($documents->isEmpty()) {
            return null;
        }

        $times = $documents->map(function ($doc) {
            return $doc->metadata['pdf_generation_time'] ?? null;
        })->filter();

        return $times->isEmpty() ? null : round($times->avg(), 3);
    }

    /**
     * Display statistics in table format
     */
    private function displayTableFormat(array $stats, bool $detailed): void
    {
        // Documents table
        $this->info('📄 Documents');
        $this->table(
            ['Metric', 'Value'],
            collect($stats['documents'])->map(function ($value, $key) {
                return [ucwords(str_replace('_', ' ', $key)), $value];
            })->toArray()
        );

        // Storage table
        $this->info('💾 Storage');
        $this->table(
            ['Metric', 'Value'],
            collect($stats['storage'])->map(function ($value, $key) {
                return [ucwords(str_replace('_', ' ', $key)), $value];
            })->toArray()
        );

        // Cache table
        $this->info('🗄️ Cache');
        $this->table(
            ['Metric', 'Value'],
            collect($stats['cache'])->map(function ($value, $key) {
                return [ucwords(str_replace('_', ' ', $key)), is_bool($value) ? ($value ? 'Yes' : 'No') : $value];
            })->toArray()
        );

        // Queue table
        $this->info('⚡ Queue');
        $this->table(
            ['Metric', 'Value'],
            collect($stats['queue'])->map(function ($value, $key) {
                return [ucwords(str_replace('_', ' ', $key)), $value];
            })->toArray()
        );

        if ($detailed && isset($stats['performance'])) {
            $this->info('🚀 Performance');
            $this->table(
                ['Metric', 'Value'],
                collect($stats['performance'])->map(function ($value, $key) {
                    return [ucwords(str_replace('_', ' ', $key)), $value];
                })->toArray()
            );
        }
    }

    /**
     * Check for critical issues
     */
    private function checkForIssues(array $stats): array
    {
        $issues = [];

        // Check database connection
        if (!($stats['database']['connection_working'] ?? true)) {
            $issues[] = 'Database connection failed';
        }

        // Check cache
        if (!($stats['cache']['cache_working'] ?? true)) {
            $issues[] = 'Cache system not working';
        }

        // Check disk space
        if (($stats['storage']['disk_free_space_gb'] ?? 0) < 1) {
            $issues[] = 'Low disk space (less than 1GB free)';
        }

        // Check for too many failed jobs
        if (($stats['queue']['failed_jobs'] ?? 0) > 100) {
            $issues[] = 'High number of failed jobs (' . $stats['queue']['failed_jobs'] . ')';
        }

        // Check for expired documents
        if (($stats['documents']['expired_documents'] ?? 0) > 1000) {
            $issues[] = 'High number of expired documents (' . $stats['documents']['expired_documents'] . ')';
        }

        return $issues;
    }
}