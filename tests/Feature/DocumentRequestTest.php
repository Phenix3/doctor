<?php

use App\Enums\Country;
use App\Http\Requests\DocumentRequest;
use Illuminate\Support\Facades\Validator;

describe('DocumentRequest Validation', function () {
    it('validates required fields correctly', function () {
        $request = new DocumentRequest;

        $data = [];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('country'))->toBeTrue();
        expect($validator->errors()->has('document_type'))->toBeTrue();
        expect($validator->errors()->has('tone_level'))->toBeTrue();
        expect($validator->errors()->has('language'))->toBeTrue();
        expect($validator->errors()->has('fields'))->toBeTrue();
    });

    it('validates country enum values correctly', function () {
        $request = new DocumentRequest;

        // Valid country
        $validData = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => ['test' => 'value'],
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        expect($validator->fails())->toBeFalse();

        // Invalid country
        $invalidData = $validData;
        $invalidData['country'] = 'INVALID_COUNTRY';

        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('country'))->toBeTrue();
    });

    it('validates document type enum values correctly', function () {
        $request = new DocumentRequest;

        // Valid document type
        $validData = [
            'country' => 'SENEGAL',
            'document_type' => 'MOTIVATION_LETTER',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => ['test' => 'value'],
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        expect($validator->fails())->toBeFalse();

        // Invalid document type
        $invalidData = $validData;
        $invalidData['document_type'] = 'INVALID_DOCUMENT_TYPE';

        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('document_type'))->toBeTrue();
    });

    it('validates tone level enum values correctly', function () {
        $request = new DocumentRequest;

        // Valid tone level
        $validData = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'VERY_OFFICIAL',
            'language' => 'fr',
            'fields' => ['test' => 'value'],
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        expect($validator->fails())->toBeFalse();

        // Invalid tone level
        $invalidData = $validData;
        $invalidData['tone_level'] = 'INVALID_TONE';

        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('tone_level'))->toBeTrue();
    });

    it('validates language correctly', function () {
        $request = new DocumentRequest;

        // Valid languages
        $validDataFr = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => ['test' => 'value'],
        ];

        $validDataEn = $validDataFr;
        $validDataEn['language'] = 'en';

        $validator = Validator::make($validDataFr, $request->rules(), $request->messages());
        expect($validator->fails())->toBeFalse();

        $validator = Validator::make($validDataEn, $request->rules(), $request->messages());
        expect($validator->fails())->toBeFalse();

        // Invalid language
        $invalidData = $validDataFr;
        $invalidData['language'] = 'es';

        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('language'))->toBeTrue();
    });

    it('validates fields array correctly', function () {
        $request = new DocumentRequest;

        // Valid fields array
        $validData = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => ['field1' => 'value1', 'field2' => 'value2'],
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        expect($validator->fails())->toBeFalse();

        // Empty fields array
        $invalidData = $validData;
        $invalidData['fields'] = [];

        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('fields'))->toBeTrue();

        // Non-array fields
        $invalidData['fields'] = 'not an array';

        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('fields'))->toBeTrue();
    });

    it('applies document type specific validation for administrative application', function () {
        // Create a mock request with the data
        $data = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => [
                'applicant_name' => 'Jean Dupont',
                'applicant_address' => '123 Rue de la Paix, Dakar',
                'applicant_city' => 'Dakar',
                'recipient_title' => 'Monsieur le Directeur',
                'recipient_organization' => 'Ministère de l\'Administration',
                'subject' => 'Demande de certificat',
                'request_context' => 'Demande administrative pour certificat',
                'request_details' => 'Je souhaite obtenir un certificat...',
                'date' => '2024-01-15',
            ],
        ];

        // Create request and set the data
        $request = new DocumentRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules(), $request->messages());
        expect($validator->fails())->toBeFalse();

        // Missing required field for administrative application
        $dataWithMissingField = $data;
        unset($dataWithMissingField['fields']['applicant_name']);

        $request2 = new DocumentRequest;
        $request2->merge($dataWithMissingField);

        $validator = Validator::make($dataWithMissingField, $request2->rules(), $request2->messages());
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('fields.applicant_name'))->toBeTrue();
    });

    it('validates name format correctly', function () {
        $data = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => [
                'applicant_name' => 'Jean-Pierre O\'Connor',
                'applicant_address' => '123 Rue de la Paix, Dakar',
                'applicant_city' => 'Dakar',
                'recipient_title' => 'Monsieur le Directeur',
                'recipient_organization' => 'Ministère de l\'Administration',
                'subject' => 'Demande de certificat',
                'request_context' => 'Demande administrative pour certificat',
                'request_details' => 'Je souhaite obtenir un certificat...',
                'date' => '2024-01-15',
            ],
        ];

        $request = new DocumentRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules(), $request->messages());
        expect($validator->fails())->toBeFalse();

        // Invalid name with numbers
        $invalidData = $data;
        $invalidData['fields']['applicant_name'] = 'Jean123';

        $request2 = new DocumentRequest;
        $request2->merge($invalidData);

        $validator = Validator::make($invalidData, $request2->rules(), $request2->messages());
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('fields.applicant_name'))->toBeTrue();
    });

    it('validates phone format correctly', function () {
        $data = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => [
                'applicant_name' => 'Jean Dupont',
                'applicant_address' => '123 Rue de la Paix, Dakar',
                'applicant_city' => 'Dakar',
                'recipient_title' => 'Monsieur le Directeur',
                'recipient_organization' => 'Ministère de l\'Administration',
                'subject' => 'Demande de certificat',
                'request_context' => 'Demande administrative pour certificat',
                'request_details' => 'Je souhaite obtenir un certificat...',
                'applicant_phone' => '+221 77 123 45 67',
                'date' => '2024-01-15',
            ],
        ];

        $request = new DocumentRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules(), $request->messages());
        expect($validator->fails())->toBeFalse();

        // Invalid phone format
        $invalidData = $data;
        $invalidData['fields']['applicant_phone'] = 'invalid-phone';

        $request2 = new DocumentRequest;
        $request2->merge($invalidData);

        $validator = Validator::make($invalidData, $request2->rules(), $request2->messages());
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('fields.applicant_phone'))->toBeTrue();
    });

    it('validates date format correctly', function () {
        $data = [
            'country' => 'SENEGAL',
            'document_type' => 'ADMINISTRATIVE_APPLICATION',
            'tone_level' => 'OFFICIAL',
            'language' => 'fr',
            'fields' => [
                'applicant_name' => 'Jean Dupont',
                'applicant_address' => '123 Rue de la Paix, Dakar',
                'applicant_city' => 'Dakar',
                'recipient_title' => 'Monsieur le Directeur',
                'recipient_organization' => 'Ministère de l\'Administration',
                'subject' => 'Demande de certificat',
                'request_context' => 'Demande administrative pour certificat',
                'request_details' => 'Je souhaite obtenir un certificat...',
                'date' => '2024-01-15',
            ],
        ];

        $request = new DocumentRequest;
        $request->merge($data);

        $validator = Validator::make($data, $request->rules(), $request->messages());
        expect($validator->fails())->toBeFalse();

        // Invalid date format
        $invalidData = $data;
        $invalidData['fields']['date'] = '15/01/2024';

        $request2 = new DocumentRequest;
        $request2->merge($invalidData);

        $validator = Validator::make($invalidData, $request2->rules(), $request2->messages());
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('fields.date'))->toBeTrue();
    });

    it('returns French error messages', function () {
        $request = new DocumentRequest;

        $data = [];

        $validator = Validator::make($data, $request->rules(), $request->messages());

        expect($validator->fails())->toBeTrue();

        $errors = $validator->errors();
        // Check for specific French messages
        expect($errors->first('country'))->toBe('Veuillez sélectionner un pays.');
        expect($errors->first('document_type'))->toBe('Veuillez sélectionner un type de document.');
        expect($errors->first('language'))->toBe('Veuillez sélectionner une langue.');
    });
});
it('validates motivation letter specific fields', function () {
    $data = [
        'country' => 'SENEGAL',
        'document_type' => 'MOTIVATION_LETTER',
        'tone_level' => 'OFFICIAL',
        'language' => 'fr',
        'fields' => [
            'applicant_name' => 'Marie Diallo',
            'applicant_address' => '456 Avenue Bourguiba, Dakar',
            'applicant_city' => 'Dakar',
            'recipient_name' => 'Madame la Directrice',
            'recipient_organization' => 'Ministère de l\'Éducation',
            'position_title' => 'Enseignant',
            'motivation_content' => 'Je souhaite postuler pour ce poste...',
            'date' => '2024-01-15',
        ],
    ];

    $request = new DocumentRequest;
    $request->merge($data);

    $validator = Validator::make($data, $request->rules(), $request->messages());
    expect($validator->fails())->toBeFalse();

    // Missing required field for motivation letter
    $invalidData = $data;
    unset($invalidData['fields']['position_title']);

    $request2 = new DocumentRequest;
    $request2->merge($invalidData);

    $validator = Validator::make($invalidData, $request2->rules(), $request2->messages());
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('fields.position_title'))->toBeTrue();
});

it('validates honor attestation specific fields', function () {
    $data = [
        'country' => 'BURKINA_FASO',
        'document_type' => 'HONOR_ATTESTATION',
        'tone_level' => 'VERY_OFFICIAL',
        'language' => 'fr',
        'fields' => [
            'declarant_name' => 'Amadou Traoré',
            'declarant_address' => '789 Rue de la République, Ouagadougou',
            'declaration_city' => 'Ouagadougou',
            'birth_date' => '1980-05-15',
            'birth_place' => 'Ouagadougou',
            'nationality' => 'Burkinabé',
            'declaration_content' => 'Je déclare sur l\'honneur que...',
            'identity_document_type' => 'Carte Nationale d\'Identité',
            'identity_document_number' => 'CNI123456789',
            'date' => '2024-01-15',
        ],
    ];

    $request = new DocumentRequest;
    $request->merge($data);

    $validator = Validator::make($data, $request->rules(), $request->messages());
    expect($validator->fails())->toBeFalse();

    // Missing required field for honor attestation
    $invalidData = $data;
    unset($invalidData['fields']['identity_document_number']);

    $request2 = new DocumentRequest;
    $request2->merge($invalidData);

    $validator = Validator::make($invalidData, $request2->rules(), $request2->messages());
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('fields.identity_document_number'))->toBeTrue();
});

it('validates email format when provided', function () {
    $data = [
        'country' => 'SENEGAL',
        'document_type' => 'ADMINISTRATIVE_APPLICATION',
        'tone_level' => 'OFFICIAL',
        'language' => 'fr',
        'fields' => [
            'applicant_name' => 'Jean Dupont',
            'applicant_address' => '123 Rue de la Paix, Dakar',
            'applicant_city' => 'Dakar',
            'recipient_title' => 'Monsieur le Directeur',
            'recipient_organization' => 'Ministère de l\'Administration',
            'subject' => 'Demande de certificat',
            'request_context' => 'Demande administrative pour certificat',
            'request_details' => 'Je souhaite obtenir un certificat...',
            'applicant_email' => 'jean.dupont@email.com',
            'date' => '2024-01-15',
        ],
    ];

    $request = new DocumentRequest;
    $request->merge($data);

    $validator = Validator::make($data, $request->rules(), $request->messages());
    expect($validator->fails())->toBeFalse();

    // Invalid email format
    $invalidData = $data;
    $invalidData['fields']['applicant_email'] = 'invalid-email';

    $request2 = new DocumentRequest;
    $request2->merge($invalidData);

    $validator = Validator::make($invalidData, $request2->rules(), $request2->messages());
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('fields.applicant_email'))->toBeTrue();
});

it('validates maximum field lengths', function () {
    $data = [
        'country' => 'SENEGAL',
        'document_type' => 'ADMINISTRATIVE_APPLICATION',
        'tone_level' => 'OFFICIAL',
        'language' => 'fr',
        'fields' => [
            'applicant_name' => str_repeat('A', 101), // Exceeds max length of 100
            'applicant_address' => '123 Rue de la Paix, Dakar',
            'applicant_city' => 'Dakar',
            'recipient_title' => 'Monsieur le Directeur',
            'recipient_organization' => 'Ministère de l\'Administration',
            'subject' => 'Demande de certificat',
            'request_context' => 'Demande administrative pour certificat',
            'request_details' => 'Je souhaite obtenir un certificat...',
            'date' => '2024-01-15',
        ],
    ];

    $request = new DocumentRequest;
    $request->merge($data);

    $validator = Validator::make($data, $request->rules(), $request->messages());
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('fields.applicant_name'))->toBeTrue();
});

it('gets required fields for document types correctly', function () {
    $adminFields = DocumentRequest::getRequiredFieldsForDocumentType('ADMINISTRATIVE_APPLICATION');
    expect($adminFields)->toContain('applicant_name');
    expect($adminFields)->toContain('subject');
    expect($adminFields)->toContain('date');

    $motivationFields = DocumentRequest::getRequiredFieldsForDocumentType('MOTIVATION_LETTER');
    expect($motivationFields)->toContain('position_title');
    expect($motivationFields)->toContain('motivation_content');

    $honorFields = DocumentRequest::getRequiredFieldsForDocumentType('HONOR_ATTESTATION');
    expect($honorFields)->toContain('declaration_content');
    expect($honorFields)->toContain('identity_document_type');

    $unknownFields = DocumentRequest::getRequiredFieldsForDocumentType('UNKNOWN_TYPE');
    expect($unknownFields)->toBeEmpty();
});
