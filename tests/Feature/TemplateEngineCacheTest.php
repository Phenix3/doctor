<?php

namespace Tests\Feature;

use App\Services\TemplateEngine;
use App\Models\DocumentTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TemplateEngineCacheTest extends TestCase
{
    use RefreshDatabase;

    private TemplateEngine $templateEngine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->templateEngine = app(TemplateEngine::class);
        
        // Seed the database with test data
        $this->seed(\Database\Seeders\DocumentTemplatesSeeder::class);
    }

    public function test_template_is_cached_after_first_retrieval()
    {
        // Clear any existing cache
        Cache::flush();
        
        // First call should hit the database
        $template1 = $this->templateEngine->getTemplate('administrative_application', 'SN', 'fr');
        
        // Verify template was retrieved
        $this->assertInstanceOf(DocumentTemplate::class, $template1);
        
        // Second call should hit the cache
        $template2 = $this->templateEngine->getTemplate('administrative_application', 'SN', 'fr');
        
        // Both should be the same instance due to caching
        $this->assertEquals($template1->id, $template2->id);
    }

    public function test_can_clear_specific_template_cache()
    {
        // Get template to cache it
        $template = $this->templateEngine->getTemplate('administrative_application', 'SN', 'fr');
        $this->assertInstanceOf(DocumentTemplate::class, $template);
        
        // Clear specific template cache
        $this->templateEngine->clearTemplateCache('administrative_application', 'SN', 'fr');
        
        // Should still be able to get template (from database)
        $template2 = $this->templateEngine->getTemplate('administrative_application', 'SN', 'fr');
        $this->assertInstanceOf(DocumentTemplate::class, $template2);
    }

    public function test_can_manually_cache_template()
    {
        $template = DocumentTemplate::getTemplate('administrative_application', 'SN', 'fr');
        $this->assertInstanceOf(DocumentTemplate::class, $template);
        
        // Manually cache the template
        $this->templateEngine->cacheTemplate('administrative_application', 'SN', 'fr', $template);
        
        // Retrieve from cache
        $cachedTemplate = $this->templateEngine->getTemplate('administrative_application', 'SN', 'fr');
        
        $this->assertEquals($template->id, $cachedTemplate->id);
    }

    public function test_cache_works_with_different_countries()
    {
        // Cache templates for different countries
        $templateSN = $this->templateEngine->getTemplate('administrative_application', 'SN', 'fr');
        $templateCI = $this->templateEngine->getTemplate('administrative_application', 'CI', 'fr');
        
        $this->assertInstanceOf(DocumentTemplate::class, $templateSN);
        $this->assertInstanceOf(DocumentTemplate::class, $templateCI);
        
        // They should be different templates
        $this->assertEquals('SN', $templateSN->country);
        $this->assertEquals('CI', $templateCI->country);
    }
}