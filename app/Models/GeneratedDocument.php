<?php

namespace App\Models;

use App\Enums\Country;
use App\Enums\DocumentType;
use App\Enums\ToneLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class GeneratedDocument extends Model
{
    use HasFactory;
    protected $fillable = [
        'session_id',
        'document_type',
        'country',
        'tone_level',
        'language',
        'content',
        'pdf_path',
        'metadata',
        'expires_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'expires_at' => 'datetime',
    ];

    /**
     * Relationship to document template
     */
    public function documentTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_type', 'document_type');
    }

    /**
     * Scope to get documents by session
     */
    public function scopeForSession(Builder $query, string $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope to get non-expired documents
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope to get expired documents for cleanup
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Check if document is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Set expiration time (default 24 hours from now)
     */
    public function setExpirationTime(int $hours = 24): void
    {
        $this->expires_at = now()->addHours($hours);
        $this->save();
    }

    /**
     * Get document by session and type
     */
    public static function getBySessionAndType(string $sessionId, string $documentType): ?self
    {
        return static::forSession($sessionId)
            ->where('document_type', $documentType)
            ->notExpired()
            ->latest()
            ->first();
    }

    /**
     * Clean up expired documents
     */
    public static function cleanupExpired(): int
    {
        $expiredDocuments = static::expired()->get();
        
        // Delete PDF files if they exist
        foreach ($expiredDocuments as $document) {
            if ($document->pdf_path && file_exists(storage_path('app/' . $document->pdf_path))) {
                unlink(storage_path('app/' . $document->pdf_path));
            }
        }
        
        return static::expired()->delete();
    }

    /**
     * Generate unique session ID
     */
    public static function generateSessionId(): string
    {
        return 'doc_' . uniqid() . '_' . time();
    }

    /**
     * Store metadata about generation parameters
     */
    public function storeGenerationMetadata(array $parameters): void
    {
        $this->metadata = array_merge($this->metadata ?? [], [
            'generation_parameters' => $parameters,
            'generated_at' => now()->toISOString(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);
        $this->save();
    }
}
