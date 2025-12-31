<?php

namespace App\Http\Requests;

use App\Enums\Country;
use App\Enums\DocumentType;
use App\Enums\ToneLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\ValidationRules\Rules\Delimited;
use ReflectionMethod;

class DocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'country' => [
                'required',
                'string',
                Rule::in($this->getEnumNames(Country::class)),
            ],
            'document_type' => [
                'required',
                'string',
                Rule::in($this->getEnumNames(DocumentType::class)),
            ],
            'tone_level' => [
                'required',
                'string',
                Rule::in($this->getEnumNames(ToneLevel::class)),
            ],
            'language' => [
                'required',
                'string',
                'in:fr,en',
            ],
            'fields' => [
                'required',
                'array',
                'min:1',
            ],
        ];

        // Add conditional validation based on document type
        $documentType = $this->input('document_type');
        
        if ($documentType) {
            $rules = array_merge($rules, $this->getDocumentTypeSpecificRules($documentType));
        }

        return $rules;
    }

    /**
     * Get enum names for validation.
     */
    protected function getEnumNames(string $enumClass): array
    {
        $reflection = new \ReflectionMethod($enumClass, 'values');
        $reflection->setAccessible(true);
        return array_keys($reflection->invoke(null));
    }

    /**
     * Get document type specific validation rules.
     */
    protected function getDocumentTypeSpecificRules(string $documentType): array
    {
        $rules = [];

        switch ($documentType) {
            case 'ADMINISTRATIVE_APPLICATION':
                $rules = array_merge($rules, [
                    'fields.applicant_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.applicant_address' => ['required', 'string', 'max:255'],
                    'fields.recipient_title' => ['required', 'string', 'max:100'],
                    'fields.subject' => ['required', 'string', 'max:200'],
                    'fields.request_details' => ['required', 'string', 'max:2000'],
                    'fields.applicant_phone' => ['nullable', 'string', 'regex:/^[\+]?[0-9\s\-\(\)]{8,20}$/'],
                    'fields.applicant_email' => ['nullable', 'email', 'max:100'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'MOTIVATION_LETTER':
                $rules = array_merge($rules, [
                    'fields.applicant_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.applicant_address' => ['required', 'string', 'max:255'],
                    'fields.recipient_name' => ['required', 'string', 'max:100'],
                    'fields.recipient_organization' => ['required', 'string', 'max:150'],
                    'fields.position_title' => ['required', 'string', 'max:100'],
                    'fields.motivation_content' => ['required', 'string', 'max:3000'],
                    'fields.qualifications' => ['nullable', 'string', 'max:1500'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'INTERNSHIP_REQUEST':
                $rules = array_merge($rules, [
                    'fields.student_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.student_address' => ['required', 'string', 'max:255'],
                    'fields.institution_name' => ['required', 'string', 'max:150'],
                    'fields.study_program' => ['required', 'string', 'max:100'],
                    'fields.internship_duration' => ['required', 'string', 'max:50'],
                    'fields.internship_period' => ['required', 'string', 'max:100'],
                    'fields.supervisor_name' => ['nullable', 'string', 'max:100'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'HONOR_ATTESTATION':
                $rules = array_merge($rules, [
                    'fields.declarant_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.declarant_address' => ['required', 'string', 'max:255'],
                    'fields.declaration_content' => ['required', 'string', 'max:2000'],
                    'fields.identity_document_type' => ['required', 'string', 'max:50'],
                    'fields.identity_document_number' => ['required', 'string', 'max:50'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'COMPLAINT_LETTER':
                $rules = array_merge($rules, [
                    'fields.complainant_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.complainant_address' => ['required', 'string', 'max:255'],
                    'fields.recipient_title' => ['required', 'string', 'max:100'],
                    'fields.complaint_subject' => ['required', 'string', 'max:200'],
                    'fields.complaint_details' => ['required', 'string', 'max:2500'],
                    'fields.incident_date' => ['nullable', 'date', 'date_format:Y-m-d'],
                    'fields.desired_resolution' => ['nullable', 'string', 'max:1000'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'ADMINISTRATIVE_ACT_REQUEST':
                $rules = array_merge($rules, [
                    'fields.requester_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.requester_address' => ['required', 'string', 'max:255'],
                    'fields.act_type' => ['required', 'string', 'max:100'],
                    'fields.justification' => ['required', 'string', 'max:2000'],
                    'fields.supporting_documents' => ['nullable', 'string', 'max:500'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'APPEAL_OR_POSTPONEMENT_LETTER':
                $rules = array_merge($rules, [
                    'fields.appellant_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.appellant_address' => ['required', 'string', 'max:255'],
                    'fields.original_decision_reference' => ['required', 'string', 'max:100'],
                    'fields.appeal_grounds' => ['required', 'string', 'max:2500'],
                    'fields.requested_action' => ['required', 'string', 'max:500'],
                    'fields.original_decision_date' => ['nullable', 'date', 'date_format:Y-m-d'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'GENERIC_ADMINISTRATIVE_CORRESPONDENCE':
                $rules = array_merge($rules, [
                    'fields.sender_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.sender_address' => ['required', 'string', 'max:255'],
                    'fields.recipient_title' => ['required', 'string', 'max:100'],
                    'fields.subject' => ['required', 'string', 'max:200'],
                    'fields.message_content' => ['required', 'string', 'max:2500'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;
        }

        return $rules;
    }

    /**
     * Get custom validation messages in French.
     */
    public function messages(): array
    {
        return [
            // General field messages
            'required' => 'Le champ :attribute est obligatoire.',
            'string' => 'Le champ :attribute doit être une chaîne de caractères.',
            'max' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
            'min' => 'Le champ :attribute doit contenir au moins :min élément(s).',
            'array' => 'Le champ :attribute doit être un tableau.',
            'date' => 'Le champ :attribute doit être une date valide.',
            'date_format' => 'Le champ :attribute doit respecter le format :format.',
            'email' => 'Le champ :attribute doit être une adresse email valide.',
            'in' => 'La valeur sélectionnée pour :attribute est invalide.',
            'regex' => 'Le format du champ :attribute est invalide.',

            // Specific field messages
            'country.required' => 'Veuillez sélectionner un pays.',
            'country.in' => 'Le pays sélectionné n\'est pas supporté.',
            'document_type.required' => 'Veuillez sélectionner un type de document.',
            'document_type.in' => 'Le type de document sélectionné n\'est pas valide.',
            'tone_level.required' => 'Veuillez sélectionner un niveau de ton.',
            'tone_level.in' => 'Le niveau de ton sélectionné n\'est pas valide.',
            'language.required' => 'Veuillez sélectionner une langue.',
            'language.in' => 'La langue sélectionnée doit être français (fr) ou anglais (en).',
            'fields.required' => 'Les champs du document sont obligatoires.',
            'fields.array' => 'Les champs du document doivent être fournis sous forme de tableau.',
            'fields.min' => 'Au moins un champ de document doit être fourni.',

            // Name validation messages
            'fields.*.regex' => 'Le nom ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
            'fields.applicant_name.regex' => 'Le nom du demandeur ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
            'fields.student_name.regex' => 'Le nom de l\'étudiant ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
            'fields.declarant_name.regex' => 'Le nom du déclarant ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
            'fields.complainant_name.regex' => 'Le nom du plaignant ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
            'fields.requester_name.regex' => 'Le nom du demandeur ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
            'fields.appellant_name.regex' => 'Le nom de l\'appelant ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',
            'fields.sender_name.regex' => 'Le nom de l\'expéditeur ne peut contenir que des lettres, espaces, tirets, apostrophes et points.',

            // Phone validation messages
            'fields.applicant_phone.regex' => 'Le numéro de téléphone doit être au format valide (8-20 caractères, chiffres, espaces, tirets, parenthèses autorisés).',

            // Address validation messages
            'fields.*.max' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
            'fields.applicant_address.max' => 'L\'adresse du demandeur ne peut pas dépasser 255 caractères.',
            'fields.student_address.max' => 'L\'adresse de l\'étudiant ne peut pas dépasser 255 caractères.',
            'fields.declarant_address.max' => 'L\'adresse du déclarant ne peut pas dépasser 255 caractères.',
            'fields.complainant_address.max' => 'L\'adresse du plaignant ne peut pas dépasser 255 caractères.',
            'fields.requester_address.max' => 'L\'adresse du demandeur ne peut pas dépasser 255 caractères.',
            'fields.appellant_address.max' => 'L\'adresse de l\'appelant ne peut pas dépasser 255 caractères.',
            'fields.sender_address.max' => 'L\'adresse de l\'expéditeur ne peut pas dépasser 255 caractères.',
        ];
    }

    /**
     * Get custom attribute names for validation messages.
     */
    public function attributes(): array
    {
        return [
            'country' => 'pays',
            'document_type' => 'type de document',
            'tone_level' => 'niveau de ton',
            'language' => 'langue',
            'fields' => 'champs du document',
            'fields.applicant_name' => 'nom du demandeur',
            'fields.applicant_address' => 'adresse du demandeur',
            'fields.applicant_phone' => 'téléphone du demandeur',
            'fields.applicant_email' => 'email du demandeur',
            'fields.recipient_title' => 'titre du destinataire',
            'fields.recipient_name' => 'nom du destinataire',
            'fields.recipient_organization' => 'organisation du destinataire',
            'fields.subject' => 'objet',
            'fields.request_details' => 'détails de la demande',
            'fields.position_title' => 'titre du poste',
            'fields.motivation_content' => 'contenu de la motivation',
            'fields.qualifications' => 'qualifications',
            'fields.student_name' => 'nom de l\'étudiant',
            'fields.student_address' => 'adresse de l\'étudiant',
            'fields.institution_name' => 'nom de l\'institution',
            'fields.study_program' => 'programme d\'études',
            'fields.internship_duration' => 'durée du stage',
            'fields.internship_period' => 'période du stage',
            'fields.supervisor_name' => 'nom du superviseur',
            'fields.declarant_name' => 'nom du déclarant',
            'fields.declarant_address' => 'adresse du déclarant',
            'fields.declaration_content' => 'contenu de la déclaration',
            'fields.identity_document_type' => 'type de pièce d\'identité',
            'fields.identity_document_number' => 'numéro de pièce d\'identité',
            'fields.complainant_name' => 'nom du plaignant',
            'fields.complainant_address' => 'adresse du plaignant',
            'fields.complaint_subject' => 'objet de la plainte',
            'fields.complaint_details' => 'détails de la plainte',
            'fields.incident_date' => 'date de l\'incident',
            'fields.desired_resolution' => 'résolution souhaitée',
            'fields.requester_name' => 'nom du demandeur',
            'fields.requester_address' => 'adresse du demandeur',
            'fields.act_type' => 'type d\'acte',
            'fields.justification' => 'justification',
            'fields.supporting_documents' => 'documents justificatifs',
            'fields.appellant_name' => 'nom de l\'appelant',
            'fields.appellant_address' => 'adresse de l\'appelant',
            'fields.original_decision_reference' => 'référence de la décision originale',
            'fields.appeal_grounds' => 'motifs du recours',
            'fields.requested_action' => 'action demandée',
            'fields.original_decision_date' => 'date de la décision originale',
            'fields.sender_name' => 'nom de l\'expéditeur',
            'fields.sender_address' => 'adresse de l\'expéditeur',
            'fields.message_content' => 'contenu du message',
            'fields.date' => 'date',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        // Add custom logic for handling validation failures if needed
        parent::failedValidation($validator);
    }

    /**
     * Get validated data with proper type casting.
     */
    public function getValidatedData(): array
    {
        $validated = $this->validated();
        
        // Ensure proper enum casting using the enum names
        $validated['country'] = Country::make($validated['country']);
        $validated['document_type'] = DocumentType::make($validated['document_type']);
        $validated['tone_level'] = ToneLevel::make($validated['tone_level']);
        
        return $validated;
    }

    /**
     * Get the required fields for a specific document type.
     */
    public static function getRequiredFieldsForDocumentType(string $documentType): array
    {
        $requiredFields = [
            'ADMINISTRATIVE_APPLICATION' => [
                'applicant_name', 'applicant_address', 'recipient_title', 
                'subject', 'request_details', 'date'
            ],
            'MOTIVATION_LETTER' => [
                'applicant_name', 'applicant_address', 'recipient_name', 
                'recipient_organization', 'position_title', 'motivation_content', 'date'
            ],
            'INTERNSHIP_REQUEST' => [
                'student_name', 'student_address', 'institution_name', 
                'study_program', 'internship_duration', 'internship_period', 'date'
            ],
            'HONOR_ATTESTATION' => [
                'declarant_name', 'declarant_address', 'declaration_content', 
                'identity_document_type', 'identity_document_number', 'date'
            ],
            'COMPLAINT_LETTER' => [
                'complainant_name', 'complainant_address', 'recipient_title', 
                'complaint_subject', 'complaint_details', 'date'
            ],
            'ADMINISTRATIVE_ACT_REQUEST' => [
                'requester_name', 'requester_address', 'act_type', 
                'justification', 'date'
            ],
            'APPEAL_OR_POSTPONEMENT_LETTER' => [
                'appellant_name', 'appellant_address', 'original_decision_reference', 
                'appeal_grounds', 'requested_action', 'date'
            ],
            'GENERIC_ADMINISTRATIVE_CORRESPONDENCE' => [
                'sender_name', 'sender_address', 'recipient_title', 
                'subject', 'message_content', 'date'
            ],
        ];

        return $requiredFields[$documentType] ?? [];
    }
}