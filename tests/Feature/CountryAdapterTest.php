<?php

use App\Services\CountryAdapter;
use App\Models\CountryRule;

beforeEach(function () {
    $this->countryAdapter = new CountryAdapter();
    
    // Seed test data
    CountryRule::create([
        'country_code' => 'SN',
        'country_name' => 'Sénégal',
        'politeness_formulas' => [
            'simple' => [
                'opening' => 'Monsieur/Madame,',
                'closing' => 'Cordialement,',
            ],
            'official' => [
                'opening' => 'Monsieur/Madame le/la Responsable,',
                'closing' => 'Veuillez agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.',
            ],
        ],
        'date_format' => 'd/m/Y',
        'address_format' => [
            'template' => '{street}, {city}, {country}',
            'order' => ['street', 'city', 'country'],
        ],
        'administrative_terminology' => [
            'application' => 'demande',
            'request' => 'requête',
        ],
        'document_structure_rules' => [
            'header_required' => true,
            'date_position' => 'top_right',
        ],
        'default_language' => 'fr',
        'is_active' => true,
    ]);
});

test('can apply politeness formulas for country and tone level', function () {
    $content = '{{opening_formula}} Contenu du document {{closing_formula}}';
    
    $result = $this->countryAdapter->applyPolitenesFormulas($content, 'SN', 'official');
    
    expect($result)->toContain('Monsieur/Madame le/la Responsable,');
    expect($result)->toContain('Veuillez agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.');
    expect($result)->not->toContain('{{opening_formula}}');
    expect($result)->not->toContain('{{closing_formula}}');
});

test('can format date according to country rules', function () {
    $date = new DateTime('2024-01-15');
    
    $result = $this->countryAdapter->formatDate($date, 'SN');
    
    expect($result)->toBe('15/01/2024');
});

test('can format address according to country rules', function () {
    $addressParts = [
        'street' => '123 Rue de la Paix',
        'city' => 'Dakar',
        'country' => 'Sénégal',
    ];
    
    $result = $this->countryAdapter->formatAddress($addressParts, 'SN');
    
    expect($result)->toBe('123 Rue de la Paix, Dakar, Sénégal');
});

test('can get administrative term for country', function () {
    $result = $this->countryAdapter->getAdministrativeTerm('application', 'SN');
    
    expect($result)->toBe('demande');
});

test('returns null for unsupported country', function () {
    $result = $this->countryAdapter->getAdministrativeTerm('application', 'XX');
    
    expect($result)->toBeNull();
});

test('can check if country is supported', function () {
    expect($this->countryAdapter->isCountrySupported('SN'))->toBeTrue();
    expect($this->countryAdapter->isCountrySupported('XX'))->toBeFalse();
});

test('can get document structure rules', function () {
    $rules = $this->countryAdapter->getDocumentStructureRules('SN');
    
    expect($rules)->toHaveKey('header_required');
    expect($rules['header_required'])->toBeTrue();
    expect($rules['date_position'])->toBe('top_right');
});

test('validates adaptation integrity', function () {
    $original = 'This is a test document with some content.';
    $adapted = 'This is a test document with some content and politeness formulas.';
    
    $result = $this->countryAdapter->validateAdaptation($original, $adapted, 'SN');
    
    expect($result)->toBeTrue();
});

test('detects significant content variation', function () {
    $original = 'Short content.';
    $adapted = 'This is a much longer adapted content with many additional words that significantly changes the original meaning and structure of the document.';
    
    $result = $this->countryAdapter->validateAdaptation($original, $adapted, 'SN');
    
    expect($result)->toBeFalse();
});