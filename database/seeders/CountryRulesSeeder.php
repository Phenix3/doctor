<?php

namespace Database\Seeders;

use App\Models\CountryRule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CountryRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countryRules = [
            [
                'country_code' => 'SN',
                'country_name' => 'Sénégal',
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
                    'template' => '{street}, {city}, {postal_code} {country}',
                    'order' => ['street', 'city', 'postal_code', 'country'],
                ],
                'administrative_terminology' => [
                    'application' => 'demande',
                    'request' => 'requête',
                    'attestation' => 'attestation',
                    'complaint' => 'plainte',
                    'appeal' => 'recours',
                    'director' => 'Directeur/Directrice',
                    'minister' => 'Ministre',
                ],
                'document_structure_rules' => [
                    'header_required' => true,
                    'date_position' => 'top_right',
                    'signature_position' => 'bottom_right',
                    'reference_required' => true,
                ],
                'default_language' => 'fr',
                'is_active' => true,
            ],
            [
                'country_code' => 'CI',
                'country_name' => 'Côte d\'Ivoire',
                'politeness_formulas' => [
                    'simple' => [
                        'opening' => 'Monsieur/Madame,',
                        'closing' => 'Cordialement,',
                    ],
                    'official' => [
                        'opening' => 'Monsieur/Madame le/la Directeur/Directrice,',
                        'closing' => 'Veuillez recevoir, Monsieur/Madame, mes salutations respectueuses.',
                    ],
                    'very_official' => [
                        'opening' => 'Excellence Monsieur/Madame le/la Ministre,',
                        'closing' => 'Veuillez agréer, Excellence, l\'expression de ma très haute considération.',
                    ],
                ],
                'date_format' => 'd/m/Y',
                'address_format' => [
                    'template' => '{street}, {city}, {country}',
                    'order' => ['street', 'city', 'country'],
                ],
                'administrative_terminology' => [
                    'application' => 'demande',
                    'request' => 'sollicitation',
                    'attestation' => 'attestation',
                    'complaint' => 'doléance',
                    'appeal' => 'recours gracieux',
                    'director' => 'Directeur/Directrice',
                    'minister' => 'Ministre',
                ],
                'document_structure_rules' => [
                    'header_required' => true,
                    'date_position' => 'top_right',
                    'signature_position' => 'bottom_right',
                    'reference_required' => true,
                ],
                'default_language' => 'fr',
                'is_active' => true,
            ],
            [
                'country_code' => 'BF',
                'country_name' => 'Burkina Faso',
                'politeness_formulas' => [
                    'simple' => [
                        'opening' => 'Monsieur/Madame,',
                        'closing' => 'Cordialement,',
                    ],
                    'official' => [
                        'opening' => 'Monsieur/Madame le/la Responsable,',
                        'closing' => 'Veuillez agréer, Monsieur/Madame, mes salutations distinguées.',
                    ],
                    'very_official' => [
                        'opening' => 'Monsieur/Madame le/la Ministre,',
                        'closing' => 'Je vous prie d\'agréer, Monsieur/Madame le/la Ministre, l\'assurance de ma haute considération.',
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
                    'director' => 'Directeur/Directrice',
                    'minister' => 'Ministre',
                ],
                'document_structure_rules' => [
                    'header_required' => true,
                    'date_position' => 'top_right',
                    'signature_position' => 'bottom_right',
                    'reference_required' => false,
                ],
                'default_language' => 'fr',
                'is_active' => true,
            ],
            [
                'country_code' => 'ML',
                'country_name' => 'Mali',
                'politeness_formulas' => [
                    'simple' => [
                        'opening' => 'Monsieur/Madame,',
                        'closing' => 'Cordialement,',
                    ],
                    'official' => [
                        'opening' => 'Monsieur/Madame le/la Responsable,',
                        'closing' => 'Veuillez agréer, Monsieur/Madame, l\'expression de mes sentiments respectueux.',
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
                    'director' => 'Directeur/Directrice',
                    'minister' => 'Ministre',
                ],
                'document_structure_rules' => [
                    'header_required' => true,
                    'date_position' => 'top_right',
                    'signature_position' => 'bottom_right',
                    'reference_required' => false,
                ],
                'default_language' => 'fr',
                'is_active' => true,
            ],
            [
                'country_code' => 'NE',
                'country_name' => 'Niger',
                'politeness_formulas' => [
                    'simple' => [
                        'opening' => 'Monsieur/Madame,',
                        'closing' => 'Cordialement,',
                    ],
                    'official' => [
                        'opening' => 'Monsieur/Madame le/la Responsable,',
                        'closing' => 'Veuillez agréer, Monsieur/Madame, mes salutations distinguées.',
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
                    'director' => 'Directeur/Directrice',
                    'minister' => 'Ministre',
                ],
                'document_structure_rules' => [
                    'header_required' => true,
                    'date_position' => 'top_right',
                    'signature_position' => 'bottom_right',
                    'reference_required' => false,
                ],
                'default_language' => 'fr',
                'is_active' => true,
            ],
            [
                'country_code' => 'CM',
                'country_name' => 'Cameroun',
                'politeness_formulas' => [
                    'simple' => [
                        'opening' => 'Monsieur/Madame,',
                        'closing' => 'Cordialement,',
                    ],
                    'official' => [
                        'opening' => 'Monsieur/Madame le/la Directeur/Directrice,',
                        'closing' => 'Veuillez agréer, Monsieur/Madame le/la Directeur/Directrice, l\'expression de mes salutations distinguées.',
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
                    'director' => 'Directeur/Directrice',
                    'minister' => 'Ministre',
                ],
                'document_structure_rules' => [
                    'header_required' => true,
                    'date_position' => 'top_right',
                    'signature_position' => 'bottom_right',
                    'reference_required' => true,
                ],
                'default_language' => 'fr',
                'is_active' => true,
            ],
            [
                'country_code' => 'TD',
                'country_name' => 'Tchad',
                'politeness_formulas' => [
                    'simple' => [
                        'opening' => 'Monsieur/Madame,',
                        'closing' => 'Cordialement,',
                    ],
                    'official' => [
                        'opening' => 'Monsieur/Madame le/la Responsable,',
                        'closing' => 'Veuillez agréer, Monsieur/Madame, mes salutations respectueuses.',
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
                    'director' => 'Directeur/Directrice',
                    'minister' => 'Ministre',
                ],
                'document_structure_rules' => [
                    'header_required' => true,
                    'date_position' => 'top_right',
                    'signature_position' => 'bottom_right',
                    'reference_required' => false,
                ],
                'default_language' => 'fr',
                'is_active' => true,
            ],
            [
                'country_code' => 'GA',
                'country_name' => 'Gabon',
                'politeness_formulas' => [
                    'simple' => [
                        'opening' => 'Monsieur/Madame,',
                        'closing' => 'Cordialement,',
                    ],
                    'official' => [
                        'opening' => 'Monsieur/Madame le/la Directeur/Directrice,',
                        'closing' => 'Veuillez recevoir, Monsieur/Madame, l\'expression de mes salutations distinguées.',
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
                    'director' => 'Directeur/Directrice',
                    'minister' => 'Ministre',
                ],
                'document_structure_rules' => [
                    'header_required' => true,
                    'date_position' => 'top_right',
                    'signature_position' => 'bottom_right',
                    'reference_required' => true,
                ],
                'default_language' => 'fr',
                'is_active' => true,
            ],
        ];

        foreach ($countryRules as $rule) {
            CountryRule::updateOrCreate(
                ['country_code' => $rule['country_code']],
                $rule
            );
        }
    }
}
