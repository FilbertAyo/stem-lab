<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use App\Support\ClientUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Competition extends Model
{
    use HasFactory, HasPublicUlid, SoftDeletes;

    public const DEFAULT_PHASE_TEMPLATE = [
        ['phase_key' => 'registration', 'name' => 'Registration', 'action_type' => 'registration', 'is_required_action' => true],
        ['phase_key' => 'idea_submission', 'name' => 'Idea Submission', 'action_type' => 'idea', 'is_required_action' => true],
        ['phase_key' => 'idea_shortlisting', 'name' => 'Idea Shortlisting', 'action_type' => 'shortlisting', 'is_required_action' => false],
        ['phase_key' => 'prototype_development', 'name' => 'Prototype Development', 'action_type' => 'prototype_development', 'is_required_action' => false],
        ['phase_key' => 'prototype_shortlisting', 'name' => 'Prototype Shortlisting', 'action_type' => 'prototype_shortlisting', 'is_required_action' => false],
        ['phase_key' => 'mentor_onboarding', 'name' => 'Mentor Onboarding', 'action_type' => 'mentor', 'is_required_action' => false],
        ['phase_key' => 'prototype_modification', 'name' => 'Prototype Modification', 'action_type' => 'prototype_modification', 'is_required_action' => false],
        ['phase_key' => 'pitch_winner', 'name' => 'Pitch & Winner', 'action_type' => 'pitch', 'is_required_action' => false],
    ];

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
        'representative_count',
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
        'representative_count' => 'integer',
        'min_age' => 'integer',
        'max_age' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $competition): void {
            if (blank($competition->slug) && filled($competition->name)) {
                $competition->slug = Str::slug($competition->name);
            }

            if (blank($competition->sms_code) && Schema::hasColumn($competition->getTable(), 'sms_code')) {
                $competition->sms_code = self::generateUniqueSmsCode();
            }
        });
    }

    /**
     * Canonical teacher URL. QR codes use this ULID path and it must keep working.
     */
    public function participantUrl(): string
    {
        return ClientUrl::route('client.competitions.show', $this);
    }

    /**
     * Short link for SMS bodies. Falls back to the ULID URL until a code exists.
     */
    public function smsUrl(): string
    {
        if (filled($this->sms_code)) {
            return ClientUrl::route('competitions.short', ['smsCode' => $this->sms_code]);
        }

        return $this->participantUrl();
    }

    public static function generateUniqueSmsCode(): string
    {
        $reserved = [
            'create', 'export', 'delete', 'update', 'search', 'import', 'status', 'result', 'report',
        ];

        for ($attempt = 0; $attempt < 25; $attempt++) {
            $candidate = strtolower(Str::random(6));

            if (! preg_match('/[a-z]/', $candidate) || in_array($candidate, $reserved, true)) {
                continue;
            }

            $exists = static::withTrashed()->where('sms_code', $candidate)->exists();
            if (! $exists) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Unable to generate a unique competition SMS code.');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(CompetitionPhase::class)->orderBy('sequence');
    }

    public function classRequirements(): HasMany
    {
        return $this->hasMany(CompetitionClassRequirement::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(CompetitionRegistration::class);
    }

    public function ideas(): HasMany
    {
        return $this->hasMany(CompetitionIdea::class);
    }

    public function judgeAssignments(): HasMany
    {
        return $this->hasMany(CompetitionJudgeAssignment::class);
    }

    public function ideaScores(): HasMany
    {
        return $this->hasMany(CompetitionIdeaScore::class);
    }

    public function shortlists(): HasMany
    {
        return $this->hasMany(CompetitionShortlist::class);
    }

    public function prototypeSubmissions(): HasMany
    {
        return $this->hasMany(CompetitionPrototypeSubmission::class);
    }

    public function prototypeShortlists(): HasMany
    {
        return $this->hasMany(CompetitionPrototypeShortlist::class);
    }

    public function prototypeModifications(): HasMany
    {
        return $this->hasMany(CompetitionPrototypeModification::class);
    }

    public function prototypeModificationShortlists(): HasMany
    {
        return $this->hasMany(CompetitionPrototypeModificationShortlist::class);
    }

    public function stageEvents(): HasMany
    {
        return $this->hasMany(CompetitionStageEvent::class)->latest();
    }

    public function isRegistrationOpen(): bool
    {
        $now = now();

        if (! in_array($this->status, ['published', 'ongoing'], true)) {
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

    public function isOngoing(): bool
    {
        $now = now();

        if (! $this->competition_start || ! $this->competition_end) {
            return false;
        }

        return $now->gte($this->competition_start) && $now->lte($this->competition_end);
    }

    public function canAcceptSubmissions(): bool
    {
        $phase = $this->currentPhase();

        if (! $phase) {
            return false;
        }

        return $phase->action_type === 'idea' && $phase->isOpenNow();
    }

    public function currentPhase(): ?CompetitionPhase
    {
        $loadedPhases = $this->relationLoaded('phases') ? $this->phases : $this->phases()->get();

        $openPhase = $loadedPhases->first(function (CompetitionPhase $phase) {
            return $phase->isOpenNow();
        });

        if ($openPhase) {
            return $openPhase;
        }

        return $loadedPhases->firstWhere('status', 'open');
    }

    public function isPhaseOpen(string $phaseKey): bool
    {
        $phase = $this->phases()->where('phase_key', $phaseKey)->first();

        return $phase?->isOpenNow() ?? false;
    }

    /**
     * Teacher self-signup on the public explore competition page.
     * Only when status is ongoing and the registration phase is open.
     * Published / “coming soon” competitions stay browse-only until an admin sets status to ongoing.
     */
    public function allowsPublicTeacherRegistration(): bool
    {
        if (! $this->is_active || $this->status !== 'ongoing') {
            return false;
        }

        return $this->isPhaseOpen('registration');
    }

    /**
     * Whether an existing teacher may open or submit competition registration
     * (includes legacy catch-up when the registration phase has closed).
     */
    public function allowsTeacherRegistration(?CompetitionRegistration $registration = null): bool
    {
        if ($this->isPhaseOpen('registration')) {
            return true;
        }

        if ($registration === null) {
            return true;
        }

        return in_array($registration->status, ['draft', 'rejected'], true);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'secondary',
            'published' => 'info',
            'ongoing' => 'success',
            'judging' => 'warning',
            'completed' => 'primary',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereIn('status', ['published', 'ongoing']);
    }

    public function scopePublicVisible(Builder $query): Builder
    {
        return $query->whereIn('status', ['published', 'ongoing', 'completed']);
    }

    public function scopeForClub(Builder $query, int|string $clubId): Builder
    {
        return $query->where('club_id', $clubId);
    }
}
