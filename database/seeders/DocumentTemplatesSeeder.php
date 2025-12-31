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
        return [
            // Administrative Application Templates
            [
                'document_type' => 'administrative_application',
                'country' => 'SN',
                'language' => 'fr',
                'template_content' => 'Blade template for administrative application',
                'required_fields' => [
                    'sender_name',
                    'sender_address',
                    'sender_city',
                    'recipient_name',
                    'recipient_organization',
                    'subject',
                    'request_context',
                    'request_details'
                ],
                'optional_fields' => [
                    'sender_phone',
                    'sender_email',
                    'recipient_title',
                    'recipient_address',
                    'recipient_city',
                    'justification',
                    'expected_outcome',
                    'attachments',
                    'sender_signature'
                ],
                'validation_rules' => [
                    'sender_email' => ['email'],
                    'sender_phone' => ['phone']
                ],
                'is_active' => true,
            ],
            [
                'document_type' => 'administrative_application',
                'country' => 'CI',
                'language' => 'fr',
                'template_content' => 'Blade template for administrative application - Ivory Coast',
                'required_fields' => [
                    'sender_name',
                    'sender_address',
                    'sender_city',
                    'recipient_name',
                    'recipient_organization',
                    'subject',
                    'request_context',
                    'request_details'
                ],
                'optional_fields' => [
                    'sender_phone',
                    'sender_email',
                    'recipient_title',
                    'recipient_address',
                    'recipient_city',
                    'justification',
                    'expected_outcome',
                    'attachments',
                    'sender_signature'
                ],
                'validation_rules' => [
                    'sender_email' => ['email'],
                    'sender_phone' => ['phone']
                ],
                'is_active' => true,
            ],

            // Motivation Letter Templates
            [
                'document_type' => 'motivation_letter',
                'country' => 'SN',
                'language' => 'fr',
                'template_content' => 'Blade template for motivation letter',
                'required_fields' => [
                    'sender_name',
                    'sender_address',
                    'sender_city',
                    'recipient_name',
                    'recipient_organization',
                    'subject',
                    'introduction',
                    'motivation_reasons'
                ],
                'optional_fields' => [
                    'sender_phone',
                    'sender_email',
                    'recipient_title',
                    'recipient_address',
                    'recipient_city',
                    'professional_experience',
                    'skills_and_qualifications',
                    'contribution_expectations',
                    'availability',
                    'attachments',
                    'sender_signature'
                ],
                'validation_rules' => [
                    'sender_email' => ['email'],
                    'sender_phone' => ['phone']
                ],
                'is_active' => true,
            ],

            // Internship Request Templates
            [
                'document_type' => 'internship_request',
                'country' => 'SN',
                'language' => 'fr',
                'template_content' => 'Blade template for internship request',
                'required_fields' => [
                    'sender_name',
                    'sender_address',
                    'sender_city',
                    'recipient_name',
                    'recipient_organization',
                    'subject',
                    'student_status',
                    'academic_program',
                    'internship_duration',
                    'internship_objectives'
                ],
                'optional_fields' => [
                    'sender_phone',
                    'sender_email',
                    'recipient_title',
                    'recipient_address',
                    'recipient_city',
                    'organization_interest',
                    'skills_contribution',
                    'supervision_requirements',
                    'attachments',
                    'sender_signature'
                ],
                'validation_rules' => [
                    'sender_email' => ['email'],
                    'sender_phone' => ['phone']
                ],
                'is_active' => true,
            ],

            // Honor Attestation Templates
            [
                'document_type' => 'honor_attestation',
                'country' => 'SN',
                'language' => 'fr',
                'template_content' => 'Blade template for honor attestation',
                'required_fields' => [
                    'declarant_name',
                    'birth_date',
                    'birth_place',
                    'nationality',
                    'address',
                    'id_type',
                    'id_number',
                    'attestation_content',
                    'declaration_city'
                ],
                'optional_fields' => [
                    'id_issue_date',
                    'id_issuing_authority',
                    'specific_declarations',
                    'additional_commitments',
                    'declarant_signature'
                ],
                'validation_rules' => [
                    'birth_date' => ['date'],
                    'id_issue_date' => ['date']
                ],
                'is_active' => true,
            ],

            // Complaint Letter Templates
            [
                'document_type' => 'complaint_letter',
                'country' => 'SN',
                'language' => 'fr',
                'template_content' => 'Blade template for complaint letter',
                'required_fields' => [
                    'complainant_name',
                    'complainant_address',
                    'complainant_city',
                    'recipient_name',
                    'recipient_organization',
                    'subject',
                    'incident_description',
                    'incident_date',
                    'requested_action'
                ],
                'optional_fields' => [
                    'complainant_phone',
                    'complainant_email',
                    'recipient_title',
                    'recipient_address',
                    'recipient_city',
                    'incident_location',
                    'parties_involved',
                    'damages_or_prejudice',
                    'evidence_description',
                    'previous_attempts',
                    'legal_references',
                    'attachments',
                    'complainant_signature'
                ],
                'validation_rules' => [
                    'complainant_email' => ['email'],
                    'complainant_phone' => ['phone'],
                    'incident_date' => ['date']
                ],
                'is_active' => true,
            ],

            // Administrative Act Request Templates
            [
                'document_type' => 'administrative_act_request',
                'country' => 'SN',
                'language' => 'fr',
                'template_content' => 'Blade template for administrative act request',
                'required_fields' => [
                    'requester_name',
                    'requester_address',
                    'requester_city',
                    'recipient_name',
                    'recipient_organization',
                    'subject',
                    'request_purpose',
                    'document_type_requested',
                    'person_concerned'
                ],
                'optional_fields' => [
                    'requester_phone',
                    'requester_email',
                    'recipient_title',
                    'recipient_address',
                    'recipient_city',
                    'relationship_to_person',
                    'specific_details',
                    'urgency_justification',
                    'delivery_method',
                    'payment_information',
                    'attachments',
                    'requester_signature'
                ],
                'validation_rules' => [
                    'requester_email' => ['email'],
                    'requester_phone' => ['phone']
                ],
                'is_active' => true,
            ],

            // Appeal or Postponement Letter Templates
            [
                'document_type' => 'appeal_or_postponement_letter',
                'country' => 'SN',
                'language' => 'fr',
                'template_content' => 'Blade template for appeal or postponement letter',
                'required_fields' => [
                    'appellant_name',
                    'appellant_address',
                    'appellant_city',
                    'recipient_name',
                    'recipient_organization',
                    'subject',
                    'original_decision_reference',
                    'decision_date',
                    'decision_description',
                    'appeal_grounds',
                    'requested_outcome'
                ],
                'optional_fields' => [
                    'appellant_phone',
                    'appellant_email',
                    'recipient_title',
                    'recipient_address',
                    'recipient_city',
                    'legal_basis',
                    'factual_arguments',
                    'new_evidence',
                    'deadline_considerations',
                    'attachments',
                    'appellant_signature'
                ],
                'validation_rules' => [
                    'appellant_email' => ['email'],
                    'appellant_phone' => ['phone'],
                    'decision_date' => ['date']
                ],
                'is_active' => true,
            ],

            // Generic Administrative Correspondence Templates
            [
                'document_type' => 'generic_administrative_correspondence',
                'country' => 'SN',
                'language' => 'fr',
                'template_content' => 'Blade template for generic administrative correspondence',
                'required_fields' => [
                    'sender_name',
                    'sender_address',
                    'sender_city',
                    'recipient_name',
                    'recipient_organization',
                    'subject',
                    'correspondence_content',
                    'main_message'
                ],
                'optional_fields' => [
                    'sender_phone',
                    'sender_email',
                    'recipient_title',
                    'recipient_address',
                    'recipient_city',
                    'supporting_details',
                    'requested_action',
                    'follow_up_information',
                    'attachments',
                    'sender_signature'
                ],
                'validation_rules' => [
                    'sender_email' => ['email'],
                    'sender_phone' => ['phone']
                ],
                'is_active' => true,
            ],
        ];
    }
}