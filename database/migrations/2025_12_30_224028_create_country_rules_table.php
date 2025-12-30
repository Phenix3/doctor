<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('country_rules', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3)->unique()->index();
            $table->string('country_name', 100);
            $table->json('politeness_formulas'); // Formulas by tone level
            $table->string('date_format', 50)->default('d/m/Y');
            $table->json('address_format'); // Address formatting rules
            $table->json('administrative_terminology'); // Country-specific terms
            $table->json('document_structure_rules'); // Document structure preferences
            $table->string('default_language', 2)->default('fr');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            // Index for fast country lookups
            $table->index(['country_code', 'is_active'], 'country_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_rules');
    }
};
