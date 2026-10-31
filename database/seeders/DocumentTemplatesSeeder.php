<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;

class DocumentTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = $this->getTemplateData();

        foreach ($templates as $template) {
            DocumentTemplate::updateOrCreate(
                [
                    'document_type' => $template['document_type'],
                    'country' => $template['country'],
                    'language' => $template['language'],
                ],
                $template
            );
        }
    }

    /**
     * Get template data for seeding
     */
    private function getTemplateData(): array
    {
        $countries = ['SN', 'CI', 'ML', 'BF', 'TG', 'NE'];
        $languages = ['fr', 'en'];
        $templates = [];

        foreach ($countries as $country) {
            foreach ($languages as $language) {
                // Administrative Application Templates
                $templates[] = [
                    'document_type' => 'ADMINISTRATIVE_APPLICATION',
                    'country' => $country,
                    'language' => $language,
                    'template_content' => 'Blade template for administrative application',
                    'required_fields' => [
                        'applicant_name',
                        'applicant_address',
                        'applicant_city',
                        'recipient_title',
                        'recipient_organization',
                        'subject',
                        'request_context',
                        'request_details',
                        'date',
                    ],
                    'optional_fields' => [
                        'applicant_phone',
                        'applicant_email',
                        'recipient_name',
                        'recipient_address',
                        'recipient_city',
                        'justification',
                        'expected_outcome',
                        'urgency_reason',
                        'deadline',
                        'contact_preference',
                        'reference_number',
                        'attachments',
                        'applicant_signature',
                        'politeness_formula_opening',
                        'politeness_formula_closing',
                    ],
                    'validation_rules' => [
                        'applicant_email' => ['email'],
                        'applicant_phone' => ['phone'],
                        'date' => ['date'],
                    ],
                    'is_active' => true,
                ];

                // Motivation Letter Templates
                $templates[] = [
                    'document_type' => 'MOTIVATION_LETTER',
                    'country' => $country,
                    'language' => $language,
                    'template_content' => 'Blade template for motivation letter',
                    'required_fields' => [
                        'applicant_name',
                        'applicant_address',
                        'applicant_city',
                        'recipient_name',
                        'recipient_organization',
                        'position_title',
                        'motivation_content',
                        'date',
                    ],
                    'optional_fields' => [
                        'applicant_phone',
                        'applicant_email',
                        'recipient_title',
                        'recipient_address',
                        'recipient_city',
                        'subject',
                        'introduction',
                        'professional_experience',
                        'skills_and_qualifications',
                        'contribution_expectations',
                        'career_objectives',
                        'availability',
                        'salary_expectations',
                        'reference_number',
                        'attachments',
                        'applicant_signature',
                        'politeness_formula_opening',
                        'politeness_formula_closing',
                    ],
                    'validation_rules' => [
                        'applicant_email' => ['email'],
                        'applicant_phone' => ['phone'],
                        'date' => ['date'],
                    ],
                    'is_active' => true,
                ];

                // Internship Request Templates
                $templates[] = [
                    'document_type' => 'INTERNSHIP_REQUEST',
                    'country' => $country,
                    'language' => $language,
                    'template_content' => 'Blade template for internship request',
                    'required_fields' => [
                        'student_name',
                        'student_address',
                        'student_city',
                        'recipient_name',
                        'recipient_organization',
                        'institution_name',
                        'study_program',
                        'internship_duration',
                        'internship_period',
                        'internship_objectives',
                        'date',
                    ],
                    'optional_fields' => [
                        'student_phone',
                        'student_email',
                        'recipient_title',
                        'recipient_address',
                        'recipient_city',
                        'subject',
                        'student_status',
                        'academic_level',
                        'organization_interest',
                        'skills_contribution',
                        'supervision_requirements',
                        'academic_supervisor',
                        'supervisor_contact',
                        'evaluation_requirements',
                        'reference_number',
                        'attachments',
                        'student_signature',
                        'politeness_formula_opening',
                        'politeness_formula_closing',
                    ],
                    'validation_rules' => [
                        'student_email' => ['email'],
                        'student_phone' => ['phone'],
                        'date' => ['date'],
                    ],
                    'is_active' => true,
                ];

                // Honor Attestation Templates
                $templates[] = [
                    'document_type' => 'HONOR_ATTESTATION',
                    'country' => $country,
                    'language' => $language,
                    'template_content' => 'Blade template for honor attestation',
                    'required_fields' => [
                        'declarant_name',
                        'declarant_address',
                        'declaration_city',
                        'birth_date',
                        'birth_place',
                        'nationality',
                        'identity_document_type',
                        'identity_document_number',
                        'declaration_content',
                        'date',
                    ],
                    'optional_fields' => [
                        'declarant_phone',
                        'declarant_email',
                        'attestation_title',
                        'profession',
                        'id_issue_date',
                        'id_issuing_authority',
                        'specific_declarations',
                        'factual_statements',
                        'period_concerned',
                        'witnesses',
                        'additional_commitments',
                        'legal_consequences',
                        'witness_signature',
                        'declarant_signature',
                    ],
                    'validation_rules' => [
                        'declarant_email' => ['email'],
                        'declarant_phone' => ['phone'],
                        'birth_date' => ['date'],
                        'id_issue_date' => ['date'],
                        'date' => ['date'],
                    ],
                    'is_active' => true,
                ];

                // Complaint Letter Templates
                $templates[] = [
                    'document_type' => 'COMPLAINT_LETTER',
                    'country' => $country,
                    'language' => $language,
                    'template_content' => 'Blade template for complaint letter',
                    'required_fields' => [
                        'complainant_name',
                        'complainant_address',
                        'complainant_city',
                        'recipient_title',
                        'recipient_organization',
                        'complaint_subject',
                        'complaint_details',
                        'desired_resolution',
                        'date',
                    ],
                    'optional_fields' => [
                        'complainant_phone',
                        'complainant_email',
                        'recipient_name',
                        'recipient_address',
                        'recipient_city',
                        'incident_date',
                        'incident_time',
                        'incident_location',
                        'parties_involved',
                        'witnesses',
                        'damages_or_prejudice',
                        'financial_impact',
                        'evidence_description',
                        'previous_attempts',
                        'urgency_justification',
                        'compensation_requested',
                        'deadline_expectation',
                        'reference_number',
                        'attachments',
                        'complainant_signature',
                        'politeness_formula_opening',
                        'politeness_formula_closing',
                    ],
                    'validation_rules' => [
                        'complainant_email' => ['email'],
                        'complainant_phone' => ['phone'],
                        'incident_date' => ['date'],
                        'date' => ['date'],
                    ],
                    'is_active' => true,
                ];

                // Administrative Act Request Templates
                $templates[] = [
                    'document_type' => 'ADMINISTRATIVE_ACT_REQUEST',
                    'country' => $country,
                    'language' => $language,
                    'template_content' => 'Blade template for administrative act request',
                    'required_fields' => [
                        'requester_name',
                        'requester_address',
                        'requester_city',
                        'recipient_title',
                        'recipient_organization',
                        'request_purpose',
                        'document_type_requested',
                        'person_concerned',
                        'justification',
                        'date',
                    ],
                    'optional_fields' => [
                        'requester_phone',
                        'requester_email',
                        'recipient_name',
                        'recipient_address',
                        'recipient_city',
                        'subject',
                        'person_birth_date',
                        'person_birth_place',
                        'relationship_to_person',
                        'specific_details',
                        'period_concerned',
                        'urgency_justification',
                        'delivery_method',
                        'delivery_address',
                        'payment_information',
                        'contact_person',
                        'contact_phone',
                        'reference_number',
                        'attachments',
                        'requester_signature',
                        'politeness_formula_opening',
                        'politeness_formula_closing',
                    ],
                    'validation_rules' => [
                        'requester_email' => ['email'],
                        'requester_phone' => ['phone'],
                        'contact_phone' => ['phone'],
                        'person_birth_date' => ['date'],
                        'date' => ['date'],
                    ],
                    'is_active' => true,
                ];

                // Appeal or Postponement Letter Templates
                $templates[] = [
                    'document_type' => 'APPEAL_OR_POSTPONEMENT_LETTER',
                    'country' => $country,
                    'language' => $language,
                    'template_content' => 'Blade template for appeal or postponement letter',
                    'required_fields' => [
                        'appellant_name',
                        'appellant_address',
                        'appellant_city',
                        'recipient_title',
                        'recipient_organization',
                        'original_decision_reference',
                        'decision_description',
                        'appeal_grounds',
                        'requested_outcome',
                        'date',
                    ],
                    'optional_fields' => [
                        'appellant_phone',
                        'appellant_email',
                        'recipient_name',
                        'recipient_address',
                        'recipient_city',
                        'subject',
                        'original_decision_date',
                        'decision_authority',
                        'notification_date',
                        'notification_method',
                        'legal_basis',
                        'factual_arguments',
                        'procedural_irregularities',
                        'new_evidence',
                        'circumstances_change',
                        'alternative_solution',
                        'deadline_considerations',
                        'interim_measures',
                        'reference_number',
                        'attachments',
                        'appellant_signature',
                        'politeness_formula_opening',
                        'politeness_formula_closing',
                    ],
                    'validation_rules' => [
                        'appellant_email' => ['email'],
                        'appellant_phone' => ['phone'],
                        'original_decision_date' => ['date'],
                        'notification_date' => ['date'],
                        'date' => ['date'],
                    ],
                    'is_active' => true,
                ];

                // Generic Administrative Correspondence Templates
                $templates[] = [
                    'document_type' => 'GENERIC_ADMINISTRATIVE_CORRESPONDENCE',
                    'country' => $country,
                    'language' => $language,
                    'template_content' => 'Blade template for generic administrative correspondence',
                    'required_fields' => [
                        'sender_name',
                        'sender_address',
                        'sender_city',
                        'recipient_title',
                        'recipient_organization',
                        'subject',
                        'message_content',
                        'date',
                    ],
                    'optional_fields' => [
                        'sender_phone',
                        'sender_email',
                        'sender_function',
                        'sender_organization',
                        'recipient_name',
                        'recipient_address',
                        'recipient_city',
                        'context',
                        'supporting_details',
                        'background_information',
                        'requested_action',
                        'deadline',
                        'follow_up_information',
                        'contact_information',
                        'next_steps',
                        'additional_notes',
                        'copy_to',
                        'reference_number',
                        'your_reference',
                        'attachments',
                        'sender_signature',
                        'politeness_formula_opening',
                        'politeness_formula_closing',
                    ],
                    'validation_rules' => [
                        'sender_email' => ['email'],
                        'sender_phone' => ['phone'],
                        'date' => ['date'],
                    ],
                    'is_active' => true,
                ];
            }
        }

        return $templates;
    }
}
