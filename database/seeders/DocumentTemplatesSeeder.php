<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'document_type' => 'administrative_application',
                'country' => 'SN',
                'language' => 'fr',
                'template_content' => $this->getAdministrativeApplicationTemplate(),
                'required_fields' => [
                    'sender_name',
                    'sender_address',
                    'recipient_name',
                    'recipient_address',
                    'object',
                    'content',
                    'date',
                    'city'
                ],
                'optional_fields' => [
                    'sender_title',
                    'recipient_title',
                    'reference_number',
                    'attachments'
                ],
                'validation_rules' => [
                    'sender_name' => 'required|string|max:255',
                    'sender_address' => 'required|string|max:500',
                    'recipient_name' => 'required|string|max:255',
                    'recipient_address' => 'required|string|max:500',
                    'object' => 'required|string|max:255',
                    'content' => 'required|string|max:2000',
                    'date' => 'required|date',
                    'city' => 'required|string|max:100'
                ],
                'is_active' => true,
            ],
            [
                'document_type' => 'motivation_letter',
                'country' => 'SN',
                'language' => 'fr',
                'template_content' => $this->getMotivationLetterTemplate(),
                'required_fields' => [
                    'sender_name',
                    'sender_address',
                    'recipient_name',
                    'recipient_address',
                    'position_title',
                    'motivation_content',
                    'date',
                    'city'
                ],
                'optional_fields' => [
                    'sender_title',
                    'recipient_title',
                    'company_name',
                    'experience_summary'
                ],
                'validation_rules' => [
                    'sender_name' => 'required|string|max:255',
                    'sender_address' => 'required|string|max:500',
                    'recipient_name' => 'required|string|max:255',
                    'recipient_address' => 'required|string|max:500',
                    'position_title' => 'required|string|max:255',
                    'motivation_content' => 'required|string|max:2000',
                    'date' => 'required|date',
                    'city' => 'required|string|max:100'
                ],
                'is_active' => true,
            ],
            [
                'document_type' => 'internship_request',
                'country' => 'SN',
                'language' => 'fr',
                'template_content' => $this->getInternshipRequestTemplate(),
                'required_fields' => [
                    'sender_name',
                    'sender_address',
                    'recipient_name',
                    'recipient_address',
                    'internship_duration',
                    'internship_period',
                    'study_program',
                    'university_name',
                    'date',
                    'city'
                ],
                'optional_fields' => [
                    'sender_title',
                    'recipient_title',
                    'supervisor_name',
                    'specific_department'
                ],
                'validation_rules' => [
                    'sender_name' => 'required|string|max:255',
                    'sender_address' => 'required|string|max:500',
                    'recipient_name' => 'required|string|max:255',
                    'recipient_address' => 'required|string|max:500',
                    'internship_duration' => 'required|string|max:100',
                    'internship_period' => 'required|string|max:100',
                    'study_program' => 'required|string|max:255',
                    'university_name' => 'required|string|max:255',
                    'date' => 'required|date',
                    'city' => 'required|string|max:100'
                ],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            DocumentTemplate::updateOrCreate(
                [
                    'document_type' => $template['document_type'],
                    'country' => $template['country'],
                    'language' => $template['language']
                ],
                $template
            );
        }
    }

    private function getAdministrativeApplicationTemplate(): string
    {
        return '{{sender_name}}
{{sender_address}}

{{recipient_name}}
{{recipient_address}}

{{city}}, le {{date}}

Objet : {{object}}

{{opening_formula}}

{{content}}

{{closing_formula}}

{{sender_name}}';
    }

    private function getMotivationLetterTemplate(): string
    {
        return '{{sender_name}}
{{sender_address}}

{{recipient_name}}
{{recipient_address}}

{{city}}, le {{date}}

Objet : Candidature pour le poste de {{position_title}}

{{opening_formula}}

{{motivation_content}}

{{closing_formula}}

{{sender_name}}';
    }

    private function getInternshipRequestTemplate(): string
    {
        return '{{sender_name}}
{{sender_address}}

{{recipient_name}}
{{recipient_address}}

{{city}}, le {{date}}

Objet : Demande de stage de {{internship_duration}}

{{opening_formula}}

Étudiant(e) en {{study_program}} à {{university_name}}, je sollicite par la présente un stage de {{internship_duration}} au sein de votre organisation pour la période {{internship_period}}.

Ce stage s\'inscrit dans le cadre de ma formation et me permettrait d\'acquérir une expérience pratique dans le domaine administratif.

Je reste à votre disposition pour tout entretien que vous jugeriez utile.

{{closing_formula}}

{{sender_name}}';
    }
}
