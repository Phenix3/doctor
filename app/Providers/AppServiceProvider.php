<?php

namespace App\Providers;

use App\Services\TemplateEngine;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register TemplateEngine as singleton
        $this->app->singleton(TemplateEngine::class, function ($app) {
            return new TemplateEngine();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
