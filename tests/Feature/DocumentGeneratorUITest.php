<?php

use App\Livewire\DocumentGenerator;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can render document generator component', function () {
    $this->actingAs($this->user);

    Livewire::test(DocumentGenerator::class)
        ->assertStatus(200)
        ->assertSee('Configuration du Document')
        ->assertSee('Pays')
        ->assertSee('Type de Document')
        ->assertSee('Niveau de Formalité')
        ->assertSee('Langue');
});

it('can handle country selection', function () {
    $this->actingAs($this->user);

    Livewire::test(DocumentGenerator::class)
        ->call('handleCountryChange', 'SENEGAL')
        ->assertSet('country', 'SENEGAL');
});

it('can handle document type selection', function () {
    $this->actingAs($this->user);

    Livewire::test(DocumentGenerator::class)
        ->call('handleDocumentTypeChange', 'ADMINISTRATIVE_APPLICATION')
        ->assertSet('document_type', 'ADMINISTRATIVE_APPLICATION')
        ->assertSet('showFieldsForm', true);
});

it('can handle tone level selection', function () {
    $this->actingAs($this->user);

    Livewire::test(DocumentGenerator::class)
        ->call('handleToneChange', 'OFFICIAL')
        ->assertSet('tone_level', 'OFFICIAL');
});

it('can handle language selection', function () {
    $this->actingAs($this->user);

    Livewire::test(DocumentGenerator::class)
        ->call('handleLanguageChange', 'fr')
        ->assertSet('language', 'fr');
});

it('shows required fields when document type is selected', function () {
    $this->actingAs($this->user);

    Livewire::test(DocumentGenerator::class)
        ->call('handleDocumentTypeChange', 'ADMINISTRATIVE_APPLICATION')
        ->assertSet('showFieldsForm', true)
        ->assertCount('requiredFields', 9); // Administrative application has 9 required fields including date
});

it('validates required fields before generation', function () {
    $this->actingAs($this->user);

    $component = Livewire::test(DocumentGenerator::class)
        ->set('country', 'SENEGAL')
        ->set('document_type', 'ADMINISTRATIVE_APPLICATION')
        ->set('tone_level', 'OFFICIAL')
        ->set('language', 'fr')
        ->call('handleDocumentTypeChange', 'ADMINISTRATIVE_APPLICATION') // This populates requiredFields
        ->call('validateFields');

    // Should have validation errors for missing required fields
    expect($component->get('validationErrors'))->not->toBeEmpty();
});

it('can reset form', function () {
    $this->actingAs($this->user);

    Livewire::test(DocumentGenerator::class)
        ->set('country', 'SENEGAL')
        ->set('document_type', 'ADMINISTRATIVE_APPLICATION')
        ->set('tone_level', 'OFFICIAL')
        ->call('resetForm')
        ->assertSet('country', '')
        ->assertSet('document_type', '')
        ->assertSet('tone_level', '')
        ->assertSet('showFieldsForm', false);
});

it('provides correct computed properties', function () {
    $this->actingAs($this->user);

    $component = Livewire::test(DocumentGenerator::class);
    
    // Test countries property
    $countries = $component->get('countries');
    expect($countries)->not->toBeEmpty();
    expect($countries->first())->toHaveKey('key');
    expect($countries->first())->toHaveKey('label');

    // Test document types property
    $documentTypes = $component->get('documentTypes');
    expect($documentTypes)->not->toBeEmpty();
    expect($documentTypes->first())->toHaveKey('key');
    expect($documentTypes->first())->toHaveKey('label');

    // Test tone levels property
    $toneLevels = $component->get('toneLevels');
    expect($toneLevels)->not->toBeEmpty();
    expect($toneLevels->first())->toHaveKey('key');
    expect($toneLevels->first())->toHaveKey('label');

    // Test languages property
    $languages = $component->get('languages');
    expect($languages)->toHaveCount(2); // French and English
    expect($languages[0]['key'])->toBe('fr');
    expect($languages[1]['key'])->toBe('en');
});

it('can access document generator route', function () {
    $this->actingAs($this->user);

    $response = $this->get(route('document-generator'));
    
    $response->assertStatus(200)
        ->assertSeeLivewire(DocumentGenerator::class);
});

it('redirects unauthenticated users', function () {
    $response = $this->get(route('document-generator'));
    
    $response->assertRedirect(route('login'));
});