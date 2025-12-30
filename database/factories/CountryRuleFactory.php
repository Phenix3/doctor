<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CountryRule>
 */
class CountryRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $countries = [
            'SN' => 'Sénégal',
            'CI' => 'Côte d\'Ivoire',
            'BF' => 'Burkina Faso',
            'ML' => 'Mali',
            'NE' => 'Niger',
            'GN' => 'Guinée',
            'BJ' => 'Bénin',
            'TG' => 'Togo'
        ];

        $countryCode = $this->faker->randomKey($countries);

        return [
            'country_code' => $countryCode,
            'country_name' => $countries[$countryCode],
            'politeness_formulas' => [
                'simple' => [
                    'opening' => 'Monsieur/Madame,',
                    'closing' => 'Cordialement,',
                ],
                'official' => [
                    'opening' => 'Monsieur/Madame le/la Responsable,',
                    'closing' => 'Veuillez agréer, Monsieur/Madame, l\'expression de mes salutations distinguées.',
                ],
                'very_official' => [
                    'opening' => 'Monsieur/Madame le/la Ministre,',
                    'closing' => 'Je vous prie d\'agréer, Monsieur/Madame le/la Ministre, l\'expression de ma très haute considération.',
                ],
            ],
            'date_format' => 'd/m/Y',
            'address_format' => [
                'template' => '{street}, {city}, {country}',
                'order' => ['street', 'city', 'country'],
            ],
            'administrative_terminology' => [
                'application' => 'demande',
                'request' => 'requête',
                'attestation' => 'attestation',
                'complaint' => 'plainte',
                'appeal' => 'recours',
            ],
            'document_structure_rules' => [
                'header_required' => true,
                'date_position' => 'top_right',
                'signature_position' => 'bottom_right',
            ],
            'default_language' => 'fr',
            'is_active' => $this->faker->boolean(95), // 95% chance of being active
        ];
    }
}
