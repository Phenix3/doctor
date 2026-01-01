<?php

namespace App\Http\Requests;

use App\Enums\Country;
use App\Enums\DocumentType;
use App\Enums\ToneLevel;
use App\Rules\ContentNeutralityRule;
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
        $language = $this->input('language', 'fr');
        $neutralityRule = new ContentNeutralityRule($language);

        switch ($documentType) {
            case 'ADMINISTRATIVE_APPLICATION':
                $rules = array_merge($rules, [
                    'fields.applicant_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.applicant_address' => ['required', 'string', 'max:255'],
                    'fields.applicant_city' => ['required', 'string', 'max:100'],
                    'fields.recipient_title' => ['required', 'string', 'max:100'],
                    'fields.recipient_name' => ['nullable', 'string', 'max:100'],
                    'fields.recipient_organization' => ['required', 'string', 'max:150'],
                    'fields.subject' => ['required', 'string', 'max:200', $neutralityRule],
                    'fields.request_context' => ['required', 'string', 'max:500', $neutralityRule],
                    'fields.request_details' => ['required', 'string', 'max:2000', $neutralityRule],
                    'fields.applicant_phone' => ['nullable', 'string', 'regex:/^[\+]?[0-9\s\-\(\)]{8,20}$/'],
                    'fields.applicant_email' => ['nullable', 'email', 'max:100'],
                    'fields.justification' => ['nullable', 'string', 'max:1500', $neutralityRule],
                    'fields.expected_outcome' => ['nullable', 'string', 'max:500', $neutralityRule],
                    'fields.urgency_reason' => ['nullable', 'string', 'max:500', $neutralityRule],
                    'fields.deadline' => ['nullable', 'string', 'max:100'],
                    'fields.contact_preference' => ['nullable', 'string', 'max:200'],
                    'fields.reference_number' => ['nullable', 'string', 'max:50'],
                    'fields.attachments' => ['nullable', 'string', 'max:500'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'MOTIVATION_LETTER':
                $rules = array_merge($rules, [
                    'fields.applicant_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.applicant_address' => ['required', 'string', 'max:255'],
                    'fields.applicant_city' => ['required', 'string', 'max:100'],
                    'fields.recipient_name' => ['required', 'string', 'max:100'],
                    'fields.recipient_organization' => ['required', 'string', 'max:150'],
                    'fields.position_title' => ['required', 'string', 'max:100'],
                    'fields.subject' => ['nullable', 'string', 'max:200', $neutralityRule],
                    'fields.introduction' => ['nullable', 'string', 'max:500', $neutralityRule],
                    'fields.motivation_content' => ['required', 'string', 'max:3000', $neutralityRule],
                    'fields.professional_experience' => ['nullable', 'string', 'max:2000', $neutralityRule],
                    'fields.skills_and_qualifications' => ['nullable', 'string', 'max:1500', $neutralityRule],
                    'fields.contribution_expectations' => ['nullable', 'string', 'max:1000', $neutralityRule],
                    'fields.career_objectives' => ['nullable', 'string', 'max:800', $neutralityRule],
                    'fields.availability' => ['nullable', 'string', 'max:200'],
                    'fields.salary_expectations' => ['nullable', 'string', 'max:200'],
                    'fields.applicant_phone' => ['nullable', 'string', 'regex:/^[\+]?[0-9\s\-\(\)]{8,20}$/'],
                    'fields.applicant_email' => ['nullable', 'email', 'max:100'],
                    'fields.reference_number' => ['nullable', 'string', 'max:50'],
                    'fields.attachments' => ['nullable', 'string', 'max:500'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'INTERNSHIP_REQUEST':
                $rules = array_merge($rules, [
                    'fields.student_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.student_address' => ['required', 'string', 'max:255'],
                    'fields.student_city' => ['required', 'string', 'max:100'],
                    'fields.recipient_name' => ['required', 'string', 'max:100'],
                    'fields.recipient_organization' => ['required', 'string', 'max:150'],
                    'fields.institution_name' => ['required', 'string', 'max:150'],
                    'fields.study_program' => ['required', 'string', 'max:100'],
                    'fields.internship_duration' => ['required', 'string', 'max:50'],
                    'fields.internship_period' => ['required', 'string', 'max:100'],
                    'fields.internship_objectives' => ['required', 'string', 'max:1500', $neutralityRule],
                    'fields.student_status' => ['nullable', 'string', 'max:100'],
                    'fields.academic_level' => ['nullable', 'string', 'max:100'],
                    'fields.organization_interest' => ['nullable', 'string', 'max:1000', $neutralityRule],
                    'fields.skills_contribution' => ['nullable', 'string', 'max:1000', $neutralityRule],
                    'fields.supervision_requirements' => ['nullable', 'string', 'max:800'],
                    'fields.academic_supervisor' => ['nullable', 'string', 'max:100'],
                    'fields.supervisor_contact' => ['nullable', 'string', 'max:200'],
                    'fields.evaluation_requirements' => ['nullable', 'string', 'max:500'],
                    'fields.student_phone' => ['nullable', 'string', 'regex:/^[\+]?[0-9\s\-\(\)]{8,20}$/'],
                    'fields.student_email' => ['nullable', 'email', 'max:100'],
                    'fields.subject' => ['nullable', 'string', 'max:200', $neutralityRule],
                    'fields.reference_number' => ['nullable', 'string', 'max:50'],
                    'fields.attachments' => ['nullable', 'string', 'max:500'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'HONOR_ATTESTATION':
                $rules = array_merge($rules, [
                    'fields.declarant_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.declarant_address' => ['required', 'string', 'max:255'],
                    'fields.declaration_city' => ['required', 'string', 'max:100'],
                    'fields.birth_date' => ['required', 'date', 'date_format:Y-m-d', 'before:today'],
                    'fields.birth_place' => ['required', 'string', 'max:100'],
                    'fields.nationality' => ['required', 'string', 'max:50'],
                    'fields.identity_document_type' => ['required', 'string', 'max:50'],
                    'fields.identity_document_number' => ['required', 'string', 'max:50'],
                    'fields.declaration_content' => ['required', 'string', 'max:2000', $neutralityRule],
                    'fields.attestation_title' => ['nullable', 'string', 'max:200'],
                    'fields.profession' => ['nullable', 'string', 'max:100'],
                    'fields.id_issue_date' => ['nullable', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
                    'fields.id_issuing_authority' => ['nullable', 'string', 'max:150'],
                    'fields.specific_declarations' => ['nullable', 'string', 'max:1500', $neutralityRule],
                    'fields.factual_statements' => ['nullable', 'string', 'max:1500', $neutralityRule],
                    'fields.period_concerned' => ['nullable', 'string', 'max:200'],
                    'fields.witnesses' => ['nullable', 'string', 'max:500'],
                    'fields.additional_commitments' => ['nullable', 'string', 'max:1000', $neutralityRule],
                    'fields.legal_consequences' => ['nullable', 'string', 'max:500'],
                    'fields.witness_signature' => ['nullable', 'string', 'max:200'],
                    'fields.declarant_phone' => ['nullable', 'string', 'regex:/^[\+]?[0-9\s\-\(\)]{8,20}$/'],
                    'fields.declarant_email' => ['nullable', 'email', 'max:100'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'COMPLAINT_LETTER':
                $rules = array_merge($rules, [
                    'fields.complainant_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.complainant_address' => ['required', 'string', 'max:255'],
                    'fields.complainant_city' => ['required', 'string', 'max:100'],
                    'fields.recipient_title' => ['required', 'string', 'max:100'],
                    'fields.recipient_name' => ['nullable', 'string', 'max:100'],
                    'fields.recipient_organization' => ['required', 'string', 'max:150'],
                    'fields.complaint_subject' => ['required', 'string', 'max:200', $neutralityRule],
                    'fields.complaint_details' => ['required', 'string', 'max:2500', $neutralityRule],
                    'fields.incident_date' => ['nullable', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
                    'fields.incident_time' => ['nullable', 'string', 'max:20'],
                    'fields.incident_location' => ['nullable', 'string', 'max:200'],
                    'fields.parties_involved' => ['nullable', 'string', 'max:1000'],
                    'fields.witnesses' => ['nullable', 'string', 'max:800'],
                    'fields.damages_or_prejudice' => ['nullable', 'string', 'max:1500', $neutralityRule],
                    'fields.financial_impact' => ['nullable', 'string', 'max:500'],
                    'fields.evidence_description' => ['nullable', 'string', 'max:1000'],
                    'fields.previous_attempts' => ['nullable', 'string', 'max:1000', $neutralityRule],
                    'fields.urgency_justification' => ['nullable', 'string', 'max:500', $neutralityRule],
                    'fields.desired_resolution' => ['required', 'string', 'max:1000', $neutralityRule],
                    'fields.compensation_requested' => ['nullable', 'string', 'max:500'],
                    'fields.deadline_expectation' => ['nullable', 'string', 'max:200'],
                    'fields.complainant_phone' => ['nullable', 'string', 'regex:/^[\+]?[0-9\s\-\(\)]{8,20}$/'],
                    'fields.complainant_email' => ['nullable', 'email', 'max:100'],
                    'fields.reference_number' => ['nullable', 'string', 'max:50'],
                    'fields.attachments' => ['nullable', 'string', 'max:500'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'ADMINISTRATIVE_ACT_REQUEST':
                $rules = array_merge($rules, [
                    'fields.requester_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.requester_address' => ['required', 'string', 'max:255'],
                    'fields.requester_city' => ['required', 'string', 'max:100'],
                    'fields.recipient_title' => ['required', 'string', 'max:100'],
                    'fields.recipient_name' => ['nullable', 'string', 'max:100'],
                    'fields.recipient_organization' => ['required', 'string', 'max:150'],
                    'fields.subject' => ['nullable', 'string', 'max:200', $neutralityRule],
                    'fields.request_purpose' => ['required', 'string', 'max:500', $neutralityRule],
                    'fields.document_type_requested' => ['required', 'string', 'max:100'],
                    'fields.person_concerned' => ['required', 'string', 'max:100'],
                    'fields.person_birth_date' => ['nullable', 'date', 'date_format:Y-m-d', 'before:today'],
                    'fields.person_birth_place' => ['nullable', 'string', 'max:100'],
                    'fields.relationship_to_person' => ['nullable', 'string', 'max:100'],
                    'fields.specific_details' => ['nullable', 'string', 'max:1000', $neutralityRule],
                    'fields.period_concerned' => ['nullable', 'string', 'max:200'],
                    'fields.justification' => ['required', 'string', 'max:2000', $neutralityRule],
                    'fields.urgency_justification' => ['nullable', 'string', 'max:500', $neutralityRule],
                    'fields.delivery_method' => ['nullable', 'string', 'max:100'],
                    'fields.delivery_address' => ['nullable', 'string', 'max:255'],
                    'fields.payment_information' => ['nullable', 'string', 'max:300'],
                    'fields.contact_person' => ['nullable', 'string', 'max:100'],
                    'fields.contact_phone' => ['nullable', 'string', 'regex:/^[\+]?[0-9\s\-\(\)]{8,20}$/'],
                    'fields.requester_phone' => ['nullable', 'string', 'regex:/^[\+]?[0-9\s\-\(\)]{8,20}$/'],
                    'fields.requester_email' => ['nullable', 'email', 'max:100'],
                    'fields.reference_number' => ['nullable', 'string', 'max:50'],
                    'fields.attachments' => ['nullable', 'string', 'max:500'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'APPEAL_OR_POSTPONEMENT_LETTER':
                $rules = array_merge($rules, [
                    'fields.appellant_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.appellant_address' => ['required', 'string', 'max:255'],
                    'fields.appellant_city' => ['required', 'string', 'max:100'],
                    'fields.recipient_title' => ['required', 'string', 'max:100'],
                    'fields.recipient_name' => ['nullable', 'string', 'max:100'],
                    'fields.recipient_organization' => ['required', 'string', 'max:150'],
                    'fields.subject' => ['nullable', 'string', 'max:200', $neutralityRule],
                    'fields.original_decision_reference' => ['required', 'string', 'max:100'],
                    'fields.original_decision_date' => ['nullable', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
                    'fields.decision_authority' => ['nullable', 'string', 'max:150'],
                    'fields.decision_description' => ['required', 'string', 'max:1500', $neutralityRule],
                    'fields.notification_date' => ['nullable', 'date', 'date_format:Y-m-d', 'before_or_equal:today'],
                    'fields.notification_method' => ['nullable', 'string', 'max:100'],
                    'fields.appeal_grounds' => ['required', 'string', 'max:2500', $neutralityRule],
                    'fields.legal_basis' => ['nullable', 'string', 'max:1000'],
                    'fields.factual_arguments' => ['nullable', 'string', 'max:2000', $neutralityRule],
                    'fields.procedural_irregularities' => ['nullable', 'string', 'max:1500', $neutralityRule],
                    'fields.new_evidence' => ['nullable', 'string', 'max:1500'],
                    'fields.circumstances_change' => ['nullable', 'string', 'max:1000', $neutralityRule],
                    'fields.requested_outcome' => ['required', 'string', 'max:500', $neutralityRule],
                    'fields.alternative_solution' => ['nullable', 'string', 'max:800', $neutralityRule],
                    'fields.deadline_considerations' => ['nullable', 'string', 'max:500'],
                    'fields.interim_measures' => ['nullable', 'string', 'max:800', $neutralityRule],
                    'fields.appellant_phone' => ['nullable', 'string', 'regex:/^[\+]?[0-9\s\-\(\)]{8,20}$/'],
                    'fields.appellant_email' => ['nullable', 'email', 'max:100'],
                    'fields.reference_number' => ['nullable', 'string', 'max:50'],
                    'fields.attachments' => ['nullable', 'string', 'max:500'],
                    'fields.date' => ['required', 'date', 'date_format:Y-m-d'],
                ]);
                break;

            case 'GENERIC_ADMINISTRATIVE_CORRESPONDENCE':
                $rules = array_merge($rules, [
                    'fields.sender_name' => ['required', 'string', 'max:100', 'regex:/^[a-zA-ZÀ-ÿ\s\-\'\.]+$/u'],
                    'fields.sender_address' => ['required', 'string', 'max:255'],
                    'fields.sender_city' => ['required', 'string', 'max:100'],
                    'fields.recipient_title' => ['required', 'string', 'max:100'],
                    'fields.recipient_name' => ['nullable', 'string', 'max:100'],
                    'fields.recipient_organization' => ['required', 'string', 'max:150'],
                    'fields.subject' => ['required', 'string', 'max:200', $neutralityRule],
                    'fields.message_content' => ['required', 'string', 'max:2500', $neutralityRule],
                    'fields.sender_function' => ['nullable', 'string', 'max:100'],
                    'fields.sender_organization' => ['nullable', 'string', 'max:150'],
                    'fields.context' => ['nullable', 'string', 'max:1000', $neutralityRule],
                    'fields.supporting_details' => ['nullable', 'string', 'max:1500', $neutralityRule],
                    'fields.background_information' => ['nullable', 'string', 'max:1200', $neutralityRule],
                    'fields.requested_action' => ['nullable', 'string', 'max:800', $neutralityRule],
                    'fields.deadline' => ['nullable', 'string', 'max:200'],
                    'fields.follow_up_information' => ['nullable', 'string', 'max:800'],
                    'fields.contact_information' => ['nullable', 'string', 'max:300'],
                    'fields.next_steps' => ['nullable', 'string', 'max:600'],
                    'fields.additional_notes' => ['nullable', 'string', 'max:800'],
                    'fields.copy_to' => ['nullable', 'string', 'max:300'],
                    'fields.sender_phone' => ['nullable', 'string', 'regex:/^[\+]?[0-9\s\-\(\)]{8,20}$/'],
                    'fields.sender_email' => ['nullable', 'email', 'max:100'],
                    'fields.reference_number' => ['nullable', 'string', 'max:50'],
                    'fields.your_reference' => ['nullable', 'string', 'max:50'],
                    'fields.attachments' => ['nullable', 'string', 'max:500'],
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

            // Content neutrality messages
            'fields.subject.content_neutrality' => 'L\'objet ne peut contenir de conseils juridiques, d\'opinions ou de références légales.',
            'fields.request_details.content_neutrality' => 'Les détails de la demande ne peuvent contenir de conseils juridiques, d\'opinions ou de références légales.',
            'fields.motivation_content.content_neutrality' => 'Le contenu de motivation ne peut contenir de conseils juridiques, d\'opinions ou de références légales.',
            'fields.qualifications.content_neutrality' => 'Les qualifications ne peuvent contenir de conseils juridiques, d\'opinions ou de références légales.',
            'fields.declaration_content.content_neutrality' => 'Le contenu de la déclaration ne peut contenir de conseils juridiques, d\'opinions ou de références légales.',
            'fields.complaint_subject.content_neutrality' => 'L\'objet de la plainte ne peut contenir de conseils juridiques, d\'opinions ou de références légales.',
            'fields.complaint_details.content_neutrality' => 'Les détails de la plainte ne peuvent contenir de conseils juridiques, d\'opinions ou de références légales.',
            'fields.desired_resolution.content_neutrality' => 'La résolution souhaitée ne peut contenir de conseils juridiques, d\'opinions ou de références légales.',
            'fields.justification.content_neutrality' => 'La justification ne peut contenir de conseils juridiques, d\'opinions ou de références légales.',
            'fields.appeal_grounds.content_neutrality' => 'Les motifs du recours ne peuvent contenir de conseils juridiques, d\'opinions ou de références légales.',
            'fields.requested_action.content_neutrality' => 'L\'action demandée ne peut contenir de conseils juridiques, d\'opinions ou de références légales.',
            'fields.message_content.content_neutrality' => 'Le contenu du message ne peut contenir de conseils juridiques, d\'opinions ou de références légales.',
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
                'applicant_name', 'applicant_address', 'applicant_city', 'recipient_title', 
                'recipient_organization', 'subject', 'request_context', 'request_details', 'date'
            ],
            'MOTIVATION_LETTER' => [
                'applicant_name', 'applicant_address', 'applicant_city', 'recipient_name', 
                'recipient_organization', 'position_title', 'motivation_content', 'date'
            ],
            'INTERNSHIP_REQUEST' => [
                'student_name', 'student_address', 'student_city', 'recipient_name', 'recipient_organization',
                'institution_name', 'study_program', 'internship_duration', 'internship_period', 
                'internship_objectives', 'date'
            ],
            'HONOR_ATTESTATION' => [
                'declarant_name', 'declarant_address', 'declaration_city', 'birth_date', 'birth_place',
                'nationality', 'identity_document_type', 'identity_document_number', 'declaration_content', 'date'
            ],
            'COMPLAINT_LETTER' => [
                'complainant_name', 'complainant_address', 'complainant_city', 'recipient_title', 
                'recipient_organization', 'complaint_subject', 'complaint_details', 'desired_resolution', 'date'
            ],
            'ADMINISTRATIVE_ACT_REQUEST' => [
                'requester_name', 'requester_address', 'requester_city', 'recipient_title', 'recipient_organization',
                'request_purpose', 'document_type_requested', 'person_concerned', 'justification', 'date'
            ],
            'APPEAL_OR_POSTPONEMENT_LETTER' => [
                'appellant_name', 'appellant_address', 'appellant_city', 'recipient_title', 'recipient_organization',
                'original_decision_reference', 'decision_description', 'appeal_grounds', 'requested_outcome', 'date'
            ],
            'GENERIC_ADMINISTRATIVE_CORRESPONDENCE' => [
                'sender_name', 'sender_address', 'sender_city', 'recipient_title', 'recipient_organization',
                'subject', 'message_content', 'date'
            ],
        ];

        return $requiredFields[$documentType] ?? [];
    }
}