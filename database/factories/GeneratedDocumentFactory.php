<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GeneratedDocument>
 */
class GeneratedDocumentFactory extends Factory
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
        $toneLevels = ['simple', 'official', 'very_official'];
        $languages = ['fr', 'en'];

        return [
            'session_id' => 'doc_' . $this->faker->uuid() . '_' . time(),
            'document_type' => $this->faker->randomElement($documentTypes),
            'country' => $this->faker->randomElement($countries),
            'tone_level' => $this->faker->randomElement($toneLevels),
            'language' => $this->faker->randomElement($languages),
            'content' => $this->faker->paragraphs(5, true),
            'pdf_path' => $this->faker->optional()->filePath(),
            'metadata' => [
                'generation_parameters' => [
                    'sender_name' => $this->faker->name(),
                    'recipient_name' => $this->faker->name(),
                    'object' => $this->faker->sentence(),
                ],
                'generated_at' => now()->toISOString(),
                'user_agent' => $this->faker->userAgent(),
                'ip_address' => $this->faker->ipv4(),
            ],
            'expires_at' => now()->addHours($this->faker->numberBetween(1, 48)),
        ];
    }
}
