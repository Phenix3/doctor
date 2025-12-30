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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 100)->index();
            $table->string('country', 3)->index();
            $table->string('language', 2)->default('fr')->index();
            $table->longText('template_content');
            $table->json('required_fields');
            $table->json('optional_fields')->nullable();
            $table->json('validation_rules')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            // Composite index for performance optimization
            $table->index(['document_type', 'country', 'language', 'is_active'], 'doc_template_lookup');
            
            // Unique constraint to prevent duplicate templates
            $table->unique(['document_type', 'country', 'language'], 'unique_template');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
