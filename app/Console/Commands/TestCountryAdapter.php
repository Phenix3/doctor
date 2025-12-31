<?php

namespace App\Console\Commands;

use App\Services\CountryAdapter;
use Illuminate\Console\Command;

class TestCountryAdapter extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:country-adapter {country=SN} {tone=official}';

    /**
     * The console command description.
     */
    protected $description = 'Test the CountryAdapter service functionality';

    /**
     * Execute the console command.
     */
    public function handle(CountryAdapter $countryAdapter): int
    {
        $countryCode = $this->argument('country');
        $toneLevel = $this->argument('tone');

        $this->info("Testing CountryAdapter for country: {$countryCode}, tone: {$toneLevel}");
        $this->newLine();

        // Test country support
        if (!$countryAdapter->isCountrySupported($countryCode)) {
            $this->error("Country {$countryCode} is not supported!");
            return 1;
        }

        $this->info("✓ Country {$countryCode} is supported");

        // Test politeness formulas
        $testContent = '{{opening_formula}}

Objet: Demande de test

Monsieur/Madame,

Je vous écris pour tester le système d\'adaptation par pays.

{{closing_formula}}

Signature';

        $adaptedContent = $countryAdapter->applyPolitenesFormulas($testContent, $countryCode, $toneLevel);
        
        $this->info("✓ Politeness formulas applied");
        $this->line("Adapted content:");
        $this->line($adaptedContent);
        $this->newLine();

        // Test date formatting
        $date = new \DateTime();
        $formattedDate = $countryAdapter->formatDate($date, $countryCode);
        $this->info("✓ Date formatted: {$formattedDate}");

        // Test address formatting
        $address = [
            'street' => '123 Rue de la République',
            'city' => 'Dakar',
            'country' => 'Sénégal'
        ];
        $formattedAddress = $countryAdapter->formatAddress($address, $countryCode);
        $this->info("✓ Address formatted: {$formattedAddress}");

        // Test administrative terms
        $term = $countryAdapter->getAdministrativeTerm('application', $countryCode);
        $this->info("✓ Administrative term 'application': {$term}");

        // Test document structure rules
        $rules = $countryAdapter->getDocumentStructureRules($countryCode);
        $this->info("✓ Document structure rules:");
        foreach ($rules as $key => $value) {
            $this->line("  - {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value));
        }

        $this->newLine();
        $this->info("All tests completed successfully!");

        return 0;
    }
}