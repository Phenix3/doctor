<?php

namespace App\Services;

use App\Enums\DocumentType;
use App\Models\DocumentTemplate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\View\View as ViewInstance;
use InvalidArgumentException;

class TemplateEngine
{
    private const CACHE_PREFIX = 'document_template_';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Render a document template with provided data
     */
    public function renderTemplate(string $documentType, array $data, string $country = 'SN', string $language = 'fr'): string
    {
        $template = $this->getTemplate($documentType, $country, $language);
        
        if (!$template) {
            throw new InvalidArgumentException("Template not found for document type: {$documentType}, country: {$country}, language: {$language}");
        }

        // Check if all required fields are provided
        $missingFields = $template->getMissingRequiredFields($data);
        if (!empty($missingFields)) {
            throw new InvalidArgumentException("Missing required fields: " . implode(', ', $missingFields));
        }

        // Get the Blade template path
        $templatePath = $this->getBladeTemplatePath($documentType);
        
        // Render the template with data
        $renderedContent = View::make($templatePath, $data)->render();
        
        // Clean up the rendered content
        return $this->sanitizeOutput($renderedContent);
    }

    /**
     * Render a document template with HTML entities decoded for final output
     */
    public function renderTemplateForExport(string $documentType, array $data, string $country = 'SN', string $language = 'fr'): string
    {
        $content = $this->renderTemplate($documentType, $data, $country, $language);
        
        // Decode HTML entities for final export
        return html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Get template from database with caching
     */
    public function getTemplate(string $documentType, string $country = 'SN', string $language = 'fr'): ?DocumentTemplate
    {
        $cacheKey = $this->getCacheKey($documentType, $country, $language);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($documentType, $country, $language) {
            return DocumentTemplate::getTemplate($documentType, $country, $language);
        });
    }

    /**
     * Cache a template
     */
    public function cacheTemplate(string $documentType, string $country, string $language, DocumentTemplate $template): void
    {
        $cacheKey = $this->getCacheKey($documentType, $country, $language);
        Cache::put($cacheKey, $template, self::CACHE_TTL);
    }

    /**
     * Clear template cache
     */
    public function clearTemplateCache(string $documentType = null, string $country = null, string $language = null): void
    {
        if ($documentType && $country && $language) {
            $cacheKey = $this->getCacheKey($documentType, $country, $language);
            Cache::forget($cacheKey);
        } else {
            // Clear all template caches
            $pattern = self::CACHE_PREFIX . '*';
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        }
    }

    /**
     * Get all supported document types
     */
    public function getSupportedDocumentTypes(): array
    {
        return array_keys(DocumentType::toArray());
    }

    /**
     * Check if a document type is supported
     */
    public function isDocumentTypeSupported(string $documentType): bool
    {
        return in_array($documentType, array_keys(DocumentType::toArray()));
    }

    /**
     * Get the Blade template path for a document type
     */
    private function getBladeTemplatePath(string $documentType): string
    {
        $templateMap = [
            'administrative_application' => 'templates.administrative_application',
            'motivation_letter' => 'templates.motivation_letter',
            'internship_request' => 'templates.internship_request',
            'honor_attestation' => 'templates.honor_attestation',
            'complaint_letter' => 'templates.complaint_letter',
            'administrative_act_request' => 'templates.administrative_act_request',
            'appeal_or_postponement_letter' => 'templates.appeal_or_postponement_letter',
            'generic_administrative_correspondence' => 'templates.generic_administrative_correspondence',
        ];

        if (!isset($templateMap[$documentType])) {
            throw new InvalidArgumentException("No Blade template found for document type: {$documentType}");
        }

        return $templateMap[$documentType];
    }

    /**
     * Generate cache key
     */
    private function getCacheKey(string $documentType, string $country, string $language): string
    {
        return self::CACHE_PREFIX . md5($documentType . '_' . $country . '_' . $language);
    }

    /**
     * Sanitize output by removing extra whitespace and empty lines
     */
    private function sanitizeOutput(string $content): string
    {
        // Remove HTML comments
        $content = preg_replace('/<!--.*?-->/s', '', $content);
        
        // Remove excessive whitespace
        $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);
        
        // Remove leading/trailing whitespace from each line
        $lines = explode("\n", $content);
        $lines = array_map('trim', $lines);
        
        // Remove completely empty lines at the beginning and end
        while (!empty($lines) && $lines[0] === '') {
            array_shift($lines);
        }
        while (!empty($lines) && $lines[count($lines) - 1] === '') {
            array_pop($lines);
        }
        
        return implode("\n", $lines);
    }

    /**
     * Validate template data against template requirements
     */
    public function validateTemplateData(string $documentType, array $data, string $country = 'SN', string $language = 'fr'): array
    {
        $template = $this->getTemplate($documentType, $country, $language);
        
        if (!$template) {
            return ['Template not found'];
        }

        $errors = [];
        $missingFields = $template->getMissingRequiredFields($data);
        
        if (!empty($missingFields)) {
            $errors[] = 'Missing required fields: ' . implode(', ', $missingFields);
        }

        // Additional validation based on template rules
        $validationRules = $template->validation_rules ?? [];
        foreach ($validationRules as $field => $rules) {
            if (isset($data[$field])) {
                $value = $data[$field];
                foreach ($rules as $rule) {
                    if (!$this->validateField($value, $rule)) {
                        $errors[] = "Field '{$field}' does not meet validation rule: {$rule}";
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Simple field validation
     */
    private function validateField($value, string $rule): bool
    {
        switch ($rule) {
            case 'required':
                return !empty($value);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'phone':
                return preg_match('/^[\+]?[0-9\s\-\(\)]{8,}$/', $value);
            case 'date':
                return strtotime($value) !== false;
            default:
                return true;
        }
    }
}