<?php

namespace App\Providers;

use App\Services\TemplateEngine;
use App\Services\CountryAdapter;
use App\Services\DocumentGeneratorService;
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

        // Register CountryAdapter as singleton
        $this->app->singleton(CountryAdapter::class, function ($app) {
            return new CountryAdapter();
        });

        // Register DocumentGeneratorService as singleton with dependency injection
        $this->app->singleton(DocumentGeneratorService::class, function ($app) {
            return new DocumentGeneratorService(
                $app->make(TemplateEngine::class),
                $app->make(CountryAdapter::class)
            );
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
