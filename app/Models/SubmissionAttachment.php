<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SubmissionAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'competition_submission_id',
        'type',
        'file_path',
        'original_filename',
        'mime_type',
        'file_size',
        'caption',
        'description',
        'order',
        'category',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the submission this attachment belongs to.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(CompetitionSubmission::class, 'competition_submission_id');
    }

    /**
     * Get the full URL to the file.
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the file size in human readable format.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        
        if ($bytes === null) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if this is an image.
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Check if this is a PDF.
     */
    public function isPdf(): bool
    {
        return $this->type === 'pdf';
    }

    /**
     * Check if this is a video.
     */
    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Get the file extension.
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->original_filename, PATHINFO_EXTENSION);
    }

    /**
     * Get icon class based on file type.
     */
    public function getIconClassAttribute(): string
    {
        return match($this->type) {
            'pdf' => 'ti ti-file-type-pdf text-danger',
            'image' => 'ti ti-photo text-success',
            'document' => 'ti ti-file-text text-info',
            'video' => 'ti ti-video text-warning',
            default => 'ti ti-file text-secondary',
        };
    }

    /**
     * Scope by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for images.
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    /**
     * Scope for PDFs.
     */
    public function scopePdfs($query)
    {
        return $query->where('type', 'pdf');
    }
}

