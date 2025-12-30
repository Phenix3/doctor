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
        Schema::create('generated_documents', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->index();
            $table->string('document_type', 100)->index();
            $table->string('country', 3)->index();
            $table->string('tone_level', 50);
            $table->string('language', 2)->default('fr');
            $table->longText('content');
            $table->string('pdf_path')->nullable();
            $table->json('metadata')->nullable(); // Store generation parameters
            $table->timestamp('expires_at')->index(); // For cleanup of temporary documents
            $table->timestamps();

            // Index for session-based lookups
            $table->index(['session_id', 'created_at'], 'session_lookup');
            
            // Index for cleanup operations
            $table->index(['expires_at', 'created_at'], 'cleanup_lookup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_documents');
    }
};
