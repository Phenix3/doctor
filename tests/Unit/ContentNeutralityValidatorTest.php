<?php

use App\Services\ContentNeutralityValidator;

beforeEach(function () {
    $this->validator = new ContentNeutralityValidator();
});

test('it validates neutral content as acceptable', function () {
    $neutralContent = 'Je soussigné Jean Dupont, demeurant à Dakar, sollicite par la présente une attestation de résidence. Cette demande est motivée par les démarches administratives en cours.';
    
    $result = $this->validator->validateContentNeutrality($neutralContent);
    
    expect($result['is_neutral'])->toBeTrue();
    expect($result['violations'])->toBeEmpty();
    expect($result['violation_count'])->toBe(0);
});

test('it detects legal advice violations', function () {
    $contentWithLegalAdvice = 'Selon la loi, vous devriez consulter un avocat. Il est obligatoire de respecter cette procédure.';
    
    $result = $this->validator->validateContentNeutrality($contentWithLegalAdvice);
    
    expect($result['is_neutral'])->toBeFalse();
    expect($result['violations'])->toHaveKey('legal_advice');
    expect(count($result['violations']['legal_advice']))->toBeGreaterThan(0);
});

test('it detects opinion violations', function () {
    $contentWithOpinions = 'Je pense que cette décision est excellente et probablement la meilleure solution.';
    
    $result = $this->validator->validateContentNeutrality($contentWithOpinions);
    
    expect($result['is_neutral'])->toBeFalse();
    expect($result['violations'])->toHaveKey('opinions');
    expect(count($result['violations']['opinions']))->toBeGreaterThan(0);
});

test('it detects personal language violations', function () {
    $contentWithPersonalLanguage = 'Personnellement, je ressens que cette situation est malheureusement compliquée.';
    
    $result = $this->validator->validateContentNeutrality($contentWithPersonalLanguage);
    
    expect($result['is_neutral'])->toBeFalse();
    expect($result['violations'])->toHaveKey('personal_language');
    expect(count($result['violations']['personal_language']))->toBeGreaterThan(0);
});

test('it detects legal reference violations', function () {
    $contentWithLegalReferences = 'Conformément à l\'article 25 du code civil et selon la jurisprudence.';
    
    $result = $this->validator->validateContentNeutrality($contentWithLegalReferences);
    
    expect($result['is_neutral'])->toBeFalse();
    expect($result['violations'])->toHaveKey('legal_references');
    expect(count($result['violations']['legal_references']))->toBeGreaterThan(0);
});

test('it detects content modification violations', function () {
    $contentWithModifications = 'Votre demande cela signifie que vous devez donc procéder autrement dit suivre la procédure.';
    
    $result = $this->validator->validateContentNeutrality($contentWithModifications);
    
    expect($result['is_neutral'])->toBeFalse();
    expect($result['violations'])->toHaveKey('content_modification');
    expect(count($result['violations']['content_modification']))->toBeGreaterThan(0);
});

test('it validates user content preservation', function () {
    $originalFields = [
        'applicant_name' => 'Jean Dupont',
        'subject' => 'Demande d\'attestation',
        'request_details' => 'Je sollicite une attestation de résidence.'
    ];
    
    // Content that preserves user data
    $preservedContent = 'Monsieur Jean Dupont présente une Demande d\'attestation. Je sollicite une attestation de résidence pour mes démarches.';
    
    $result = $this->validator->validateContentNeutrality($preservedContent, $originalFields);
    
    expect($result['is_neutral'])->toBeTrue();
    expect($result['violations'])->toBeEmpty();
});

test('it detects user content modification violations', function () {
    $originalFields = [
        'applicant_name' => 'Jean Dupont',
        'request_details' => 'Je sollicite une attestation spécifique.'
    ];
    
    // Content that modifies user data (missing "spécifique")
    $modifiedContent = 'Monsieur Jean Dupont présente une demande. Je sollicite une attestation pour mes démarches.';
    
    $result = $this->validator->validateContentNeutrality($modifiedContent, $originalFields);
    
    expect($result['is_neutral'])->toBeFalse();
    expect($result['violations'])->toHaveKey('user_content_modification');
    expect(count($result['violations']['user_content_modification']))->toBeGreaterThan(0);
});

test('it provides human readable violation messages in french', function () {
    $contentWithViolations = 'Je pense que selon la loi vous devriez consulter l\'article 15.';
    
    $result = $this->validator->validateContentNeutrality($contentWithViolations);
    $messages = $this->validator->getViolationMessages($result['violations'], 'fr');
    
    expect($messages)->not->toBeEmpty();
    expect($messages)->toBeArray();
    
    foreach ($messages as $message) {
        expect($message)->toHaveKey('type');
        expect($message)->toHaveKey('message');
        expect($message)->toHaveKey('count');
        expect($message['message'])->toBeString();
        expect($message['count'])->toBeGreaterThan(0);
    }
});

test('it provides human readable violation messages in english', function () {
    $contentWithViolations = 'I think that according to law you should consult article 15.';
    
    $result = $this->validator->validateContentNeutrality($contentWithViolations);
    $messages = $this->validator->getViolationMessages($result['violations'], 'en');
    
    expect($messages)->not->toBeEmpty();
    expect($messages)->toBeArray();
    
    foreach ($messages as $message) {
        expect($message)->toHaveKey('type');
        expect($message)->toHaveKey('message');
        expect($message)->toHaveKey('count');
        expect($message['message'])->toBeString();
        expect($message['count'])->toBeGreaterThan(0);
    }
});

test('it has is content neutral convenience method', function () {
    $neutralContent = 'Demande administrative standard sans violation.';
    $violatingContent = 'Je pense que vous devriez consulter la loi.';
    
    expect($this->validator->isContentNeutral($neutralContent))->toBeTrue();
    expect($this->validator->isContentNeutral($violatingContent))->toBeFalse();
});

test('it sanitizes content without modifying meaning', function () {
    $contentWithHtml = '<p>Demande administrative</p> avec &eacute;l&eacute;ments HTML.';
    
    $result = $this->validator->sanitizeContent($contentWithHtml);
    
    expect($result['was_modified'])->toBeTrue();
    expect($result['content'])->not->toContain('<p>');
    expect($result['content'])->not->toContain('&eacute;');
    expect($result['content'])->toContain('Demande administrative');
    expect($result['content'])->toContain('éléments HTML');
});

test('it handles empty and null content', function () {
    $emptyResult = $this->validator->validateContentNeutrality('');
    expect($emptyResult['is_neutral'])->toBeTrue();
    expect($emptyResult['violations'])->toBeEmpty();
    
    $whitespaceResult = $this->validator->validateContentNeutrality('   ');
    expect($whitespaceResult['is_neutral'])->toBeTrue();
    expect($whitespaceResult['violations'])->toBeEmpty();
});

test('it detects multiple violation types in single content', function () {
    $complexViolatingContent = 'Je pense que selon la loi vous devriez personnellement consulter l\'article 15 du code civil car cela signifie que c\'est obligatoire.';
    
    $result = $this->validator->validateContentNeutrality($complexViolatingContent);
    
    expect($result['is_neutral'])->toBeFalse();
    expect(count($result['violations']))->toBeGreaterThan(1);
    expect($result['violation_count'])->toBeGreaterThan(3);
    
    // Should detect multiple types
    expect($result['violations'])->toHaveKey('opinions');
    expect($result['violations'])->toHaveKey('legal_advice');
    expect($result['violations'])->toHaveKey('personal_language');
    expect($result['violations'])->toHaveKey('legal_references');
    expect($result['violations'])->toHaveKey('content_modification');
});