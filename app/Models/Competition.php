<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Competition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'terms_and_conditions',
        'instructions',
        'cover_image',
        'club_id',
        'min_members',
        'max_members',
        'min_age',
        'max_age',
        'registration_start',
        'registration_deadline',
        'competition_start',
        'competition_end',
        'results_announcement_date',
        'status',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'registration_start' => 'datetime',
        'registration_deadline' => 'datetime',
        'competition_start' => 'datetime',
        'competition_end' => 'datetime',
        'results_announcement_date' => 'datetime',
        'is_active' => 'boolean',
        'min_members' => 'integer',
        'max_members' => 'integer',
        'min_age' => 'integer',
        'max_age' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($competition) {
            if (empty($competition->slug)) {
                $competition->slug = Str::slug($competition->name);
            }
        });
    }

    /**
     * Get the club this competition belongs to.
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Get the admin who created this competition.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the awards for this competition.
     */
    public function awards(): HasMany
    {
        return $this->hasMany(CompetitionAward::class)->orderBy('position');
    }

    /**
     * Get the participants for this competition.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(CompetitionParticipant::class);
    }

    /**
     * Check if registration is currently open.
     */
    public function isRegistrationOpen(): bool
    {
        $now = now();
        
        if ($this->status !== 'published' && $this->status !== 'ongoing') {
            return false;
        }

        if ($this->registration_start && $now->lt($this->registration_start)) {
            return false;
        }

        if ($this->registration_deadline && $now->gt($this->registration_deadline)) {
            return false;
        }

        return true;
    }

    /**
     * Check if the competition is currently active/ongoing.
     */
    public function isOngoing(): bool
    {
        $now = now();
        return $now->gte($this->competition_start) && $now->lte($this->competition_end);
    }

    /**
     * Check if submissions are allowed.
     */
    public function canAcceptSubmissions(): bool
    {
        return in_array($this->status, ['ongoing', 'published']) && $this->isOngoing();
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft' => 'secondary',
            'published' => 'info',
            'ongoing' => 'success',
            'judging' => 'warning',
            'completed' => 'primary',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Scope for active competitions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for published competitions.
     */
    public function scopePublished($query)
    {
        return $query->whereIn('status', ['published', 'ongoing']);
    }

    /**
     * Scope for public visible competitions.
     */
    public function scopePublicVisible($query)
    {
        return $query->whereIn('status', ['published', 'ongoing', 'completed']);
    }

    /**
     * Scope for competitions by club.
     */
    public function scopeForClub($query, $clubId)
    {
        return $query->where('club_id', $clubId);
    }
}

