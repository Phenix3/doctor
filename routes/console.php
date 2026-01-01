<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\CleanupExpiredDocuments;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule document cleanup job
Schedule::job(new CleanupExpiredDocuments())
    ->daily()
    ->at('02:00')
    ->name('cleanup-expired-documents')
    ->withoutOverlapping();

// Schedule cache cleanup
Schedule::command('cache:prune-stale-tags')
    ->hourly()
    ->name('cache-cleanup')
    ->withoutOverlapping();

// Schedule queue monitoring
Schedule::command('queue:monitor default,pdf-generation,cleanup --max=1000')
    ->everyFiveMinutes()
    ->name('queue-monitoring');
