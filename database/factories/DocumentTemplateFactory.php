<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentTemplate>
 */
class DocumentTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $documentTypes = [
            'administrative_application',
            'motivation_letter',
            'internship_request',
            'honor_attestation',
            'complaint_letter',
            'administrative_act_request',
            'appeal_or_postponement_letter',
            'generic_administrative_correspondence'
        ];

        $countries = ['SN', 'CI', 'BF', 'ML', 'NE', 'GN', 'BJ', 'TG'];
        $languages = ['fr', 'en'];

        return [
            'document_type' => $this->faker->randomElement($documentTypes),
            'country' => $this->faker->randomElement($countries),
            'language' => $this->faker->randomElement($languages),
            'template_content' => $this->faker->text(1000),
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
                'reference_number'
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
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }
}
