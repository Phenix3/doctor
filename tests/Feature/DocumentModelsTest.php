<?php

use App\Models\DocumentTemplate;
use App\Models\CountryRule;
use App\Models\GeneratedDocument;
use App\Enums\DocumentType;
use App\Enums\ToneLevel;
use App\Enums\Country;

beforeEach(function () {
    $this->seed([
        \Database\Seeders\CountryRulesSeeder::class,
        \Database\Seeders\DocumentTemplatesSeeder::class,
    ]);
});

test('document template can be created and retrieved', function () {
    $template = DocumentTemplate::factory()->create([
        'document_type' => 'motivation_letter',
        'country' => 'CI',
        'language' => 'fr',
        'is_active' => true,
    ]);

    expect($template->document_type)->toBe('motivation_letter');
    expect($template->country)->toBe('CI');
    expect($template->language)->toBe('fr');
    expect($template->is_active)->toBeTrue();
});

test('country rule can be retrieved and methods work', function () {
    $countryRule = CountryRule::getByCountry('SN');
    
    expect($countryRule)->not->toBeNull();
    expect($countryRule->country_code)->toBe('SN');
    expect($countryRule->country_name)->toBe('Sénégal');
    
    $openingFormula = $countryRule->getOpeningFormula('official');
    expect($openingFormula)->not->toBeNull();
    expect($openingFormula)->toContain('Monsieur/Madame');
});

test('document template can find template by criteria', function () {
    $template = DocumentTemplate::getTemplate('administrative_application', 'SN', 'fr');
    
    expect($template)->not->toBeNull();
    expect($template->document_type)->toBe('administrative_application');
    expect($template->country)->toBe('SN');
});

test('document template validates required fields correctly', function () {
    $template = DocumentTemplate::first();
    
    $validFields = [
        'sender_name' => 'John Doe',
        'sender_address' => '123 Main St',
        'sender_city' => 'Dakar',
        'recipient_name' => 'Jane Smith',
        'recipient_organization' => 'Test Organization',
        'subject' => 'Test Subject',
        'request_context' => 'Test context',
        'request_details' => 'Test details'
    ];
    
    expect($template->hasAllRequiredFields($validFields))->toBeTrue();
    
    $invalidFields = [
        'sender_name' => 'John Doe',
        // Missing other required fields
    ];
    
    expect($template->hasAllRequiredFields($invalidFields))->toBeFalse();
    
    $missingFields = $template->getMissingRequiredFields($invalidFields);
    expect($missingFields)->toContain('sender_address');
});

test('generated document can be created with session management', function () {
    $sessionId = GeneratedDocument::generateSessionId();
    
    $document = GeneratedDocument::factory()->create([
        'session_id' => $sessionId,
        'document_type' => 'administrative_application',
        'expires_at' => now()->addHours(24),
    ]);
    
    expect($document->session_id)->toBe($sessionId);
    expect($document->isExpired())->toBeFalse();
    
    // Test session retrieval
    $retrieved = GeneratedDocument::getBySessionAndType($sessionId, 'administrative_application');
    expect($retrieved)->not->toBeNull();
    expect($retrieved->id)->toBe($document->id);
});

test('enums work correctly', function () {
    expect(DocumentType::ADMINISTRATIVE_APPLICATION()->value)->toBe('administrative_application');
    expect(ToneLevel::OFFICIAL()->value)->toBe('official');
    expect(Country::SENEGAL()->value)->toBe('SN');
    
    // Test enum labels
    expect(DocumentType::ADMINISTRATIVE_APPLICATION()->label)->toBe('Demande Administrative');
    expect(ToneLevel::OFFICIAL()->label)->toBe('Officiel');
    expect(Country::SENEGAL()->label)->toBe('Sénégal');
});
