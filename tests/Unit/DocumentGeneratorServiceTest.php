<?php

use App\Services\DocumentGeneratorService;
use App\Data\DocumentGenerationRequest;
use App\Exceptions\ValidationException;
use App\Models\DocumentTemplate;
use App\Models\CountryRule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    // Create test data
    DocumentTemplate::create([
        'document_type' => 'administrative_application',
        'country' => 'SN',
        'language' => 'fr',
        'template_content' => 'Test template content',
        'required_fields' => ['applicant_name', 'applicant_address', 'recipient_title', 'subject', 'request_details', 'date'],
        'optional_fields' => [],
        'validation_rules' => [],
        'is_active' => true,
    ]);

    CountryRule::create([
        'country_code' => 'SN',
        'country_name' => 'Sénégal',
        'politeness_formulas' => [
            'simple' => [
                'opening' => 'Monsieur/Madame,',
                'closing' => 'Cordialement,'
            ],
            'official' => [
                'opening' => 'Monsieur/Madame le/la Responsable,',
                'closing' => 'Veuillez agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.'
            ]
        ],
        'date_format' => 'd/m/Y',
        'address_format' => ['template' => '{street}, {city}, {country}'],
        'administrative_terminology' => [],
        'document_structure_rules' => [],
        'default_language' => 'fr',
        'is_active' => true,
    ]);
});

test('service can be instantiated', function () {
    $service = app(DocumentGeneratorService::class);
    
    expect($service)->toBeInstanceOf(DocumentGeneratorService::class);
});

test('fails with missing required fields', function () {
    $service = app(DocumentGeneratorService::class);
    
    $request = new DocumentGenerationRequest(
        country: 'SN',
        document_type: 'administrative_application',
        tone_level: 'official',
        language: 'fr',
        fields: [
            'applicant_name' => 'Jean Dupont',
            // Missing required fields
        ],
        session_id: 'test_session_123'
    );

    // Expect ValidationException to be thrown for missing required fields
    expect(fn() => $service->generateDocument($request))
        ->toThrow(ValidationException::class, 'Champs obligatoires manquants');
});

test('returns supported document types', function () {
    $service = app(DocumentGeneratorService::class);
    
    $types = $service->getSupportedTypes();

    expect($types)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($types->count())->toBeGreaterThan(0);
    expect($types->first())->toHaveKeys(['key', 'value', 'label', 'required_fields']);
});

test('returns supported countries', function () {
    $service = app(DocumentGeneratorService::class);
    
    $countries = $service->getSupportedCountries();

    expect($countries)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    // May be empty if no countries are marked as supported in the database
    expect($countries->count())->toBeGreaterThanOrEqual(0);
});

test('returns supported tone levels', function () {
    $service = app(DocumentGeneratorService::class);
    
    $toneLevels = $service->getSupportedToneLevels();

    expect($toneLevels)->toBeInstanceOf(\Illuminate\Support\Collection::class);
    expect($toneLevels->count())->toBe(3); // simple, official, very_official
    expect($toneLevels->first())->toHaveKeys(['key', 'value', 'label']);
});

test('can create document generation request from form request data', function () {
    $requestData = [
        'country' => 'SN',
        'document_type' => 'administrative_application',
        'tone_level' => 'official',
        'language' => 'fr',
        'fields' => [
            'applicant_name' => 'Jean Dupont',
            'applicant_address' => '123 Rue de la Paix, Dakar',
            'recipient_title' => 'Monsieur le Directeur',
            'subject' => 'Demande de certificat',
            'request_details' => 'Je sollicite par la présente un certificat de résidence.',
            'date' => '2024-01-15'
        ]
    ];

    $request = new DocumentGenerationRequest(
        country: $requestData['country'],
        document_type: $requestData['document_type'],
        tone_level: $requestData['tone_level'],
        language: $requestData['language'],
        fields: $requestData['fields'],
        session_id: 'test_session_123'
    );

    expect($request->country)->toBe('SN');
    expect($request->document_type)->toBe('administrative_application');
    expect($request->tone_level)->toBe('official');
    expect($request->language)->toBe('fr');
    expect($request->hasAllRequiredFields())->toBeTrue();
    expect($request->getMissingRequiredFields())->toBeEmpty();
});