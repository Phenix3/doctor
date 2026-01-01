<?php

namespace App\Console\Commands;

use App\Jobs\CleanupExpiredDocuments as CleanupJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:cleanup 
                            {--force : Force cleanup without confirmation}
                            {--queue= : Specify queue to run the job on}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired documents and associated files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (!$this->option('force') && !$this->confirm('This will permanently delete expired documents and their files. Continue?')) {
            $this->info('Cleanup cancelled.');
            return self::SUCCESS;
        }

        $this->info('Starting document cleanup...');

        try {
            $queue = $this->option('queue') ?? 'cleanup';
            
            // Dispatch the cleanup job
            CleanupJob::dispatch()->onQueue($queue);
            
            $this->info("Cleanup job dispatched to queue: {$queue}");
            $this->info('You can monitor the job progress in your queue worker logs.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to dispatch cleanup job: ' . $e->getMessage());
            Log::error('Cleanup command failed', ['error' => $e->getMessage()]);
            
            return self::FAILURE;
        }
    }
}