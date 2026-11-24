<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompetitionRegistration extends Model
{
    use HasFactory, HasPublicUlid, SoftDeletes;

    protected $fillable = [
        'competition_id',
        'teacher_club_id',
        'teacher_user_id',
        'school_id',
        'region_id',
        'district_id',
        'status',
        'teacher_name',
        'teacher_email',
        'teacher_phone',
        'school_name',
        'school_district',
        'school_ward',
        'student_count_snapshot',
        'girls_count_snapshot',
        'girls_ratio_snapshot',
        'eligibility_met',
        'rejection_reason',
        'submitted_at',
        'approved_at',
        'approved_by',
        'eligibility_reminder_count',
        'last_eligibility_reminder_sent_at',
        'idea_submission_reminder_count',
        'last_idea_submission_reminder_sent_at',
    ];

    protected $casts = [
        'student_count_snapshot' => 'integer',
        'girls_count_snapshot' => 'integer',
        'girls_ratio_snapshot' => 'decimal:2',
        'eligibility_met' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'eligibility_reminder_count' => 'integer',
        'last_eligibility_reminder_sent_at' => 'datetime',
        'idea_submission_reminder_count' => 'integer',
        'last_idea_submission_reminder_sent_at' => 'datetime',
    ];

    public static function maxEligibilityReminders(): int
    {
        return max(1, (int) config('competitions.max_eligibility_reminders', 3));
    }

    public static function maxIdeaSubmissionReminders(): int
    {
        return max(1, (int) config('competitions.max_idea_submission_reminders', 3));
    }

    public function needsEligibilityCompletion(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Approved schools that have not final-submitted an idea (none, or draft only).
     */
    public function needsIdeaSubmission(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $idea = $this->relationLoaded('idea') ? $this->idea : $this->idea()->first();

        return ! $idea || $idea->status === 'draft';
    }

    /**
     * Admin "Pass phase" override approves a registration without meeting pool eligibility.
     * Those schools must still be able to submit ideas in later phases.
     */
    public function passedViaEligibilityOverride(): bool
    {
        return $this->status === 'approved' && ! $this->eligibility_met;
    }

    public function canSendEligibilityReminder(): bool
    {
        if (! $this->needsEligibilityCompletion()) {
            return false;
        }

        $hasEmail = filled($this->resolvedTeacherEmail());
        $hasPhone = filled($this->resolvedTeacherPhone());

        if (! $hasEmail && ! $hasPhone) {
            return false;
        }

        return (int) $this->eligibility_reminder_count < self::maxEligibilityReminders();
    }

    public function canSendIdeaSubmissionReminder(): bool
    {
        if (! $this->needsIdeaSubmission()) {
            return false;
        }

        $hasEmail = filled($this->resolvedTeacherEmail());
        $hasPhone = filled($this->resolvedTeacherPhone());

        if (! $hasEmail && ! $hasPhone) {
            return false;
        }

        return (int) $this->idea_submission_reminder_count < self::maxIdeaSubmissionReminders();
    }

    public function resolvedTeacherName(): string
    {
        return (string) ($this->teacher?->name ?: $this->teacher_name ?: '');
    }

    public function resolvedTeacherEmail(): string
    {
        return (string) ($this->teacher?->email ?: $this->teacher_email ?: '');
    }

    public function resolvedTeacherPhone(): ?string
    {
        $phone = $this->teacher?->phone ?: $this->teacher_phone;

        return filled($phone) ? (string) $phone : null;
    }

    public function resolvedSchoolName(): string
    {
        return (string) ($this->school?->name ?: $this->school_name ?: '');
    }

    public function resolvedSchoolWard(): ?string
    {
        $ward = $this->school?->ward ?? $this->school_ward;

        return filled($ward) ? (string) $ward : null;
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function teacherClub(): BelongsTo
    {
        return $this->belongsTo(TeacherClub::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function districtModel(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function districtName(): ?string
    {
        return $this->districtModel?->name ?? $this->school_district;
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function representativeStudents(): HasMany
    {
        return $this->hasMany(CompetitionRegistrationStudent::class);
    }

    public function idea(): HasOne
    {
        return $this->hasOne(CompetitionIdea::class);
    }

    public function prototypeSubmission(): HasOneThrough
    {
        return $this->hasOneThrough(
            CompetitionPrototypeSubmission::class,
            CompetitionIdea::class,
            'competition_registration_id',
            'competition_idea_id',
            'id',
            'id'
        );
    }

    public function prototypeModification(): HasOneThrough
    {
        return $this->hasOneThrough(
            CompetitionPrototypeModification::class,
            CompetitionIdea::class,
            'competition_registration_id',
            'competition_idea_id',
            'id',
            'id'
        );
    }

    public function shortlist(): HasOneThrough
    {
        return $this->hasOneThrough(
            CompetitionShortlist::class,
            CompetitionIdea::class,
            'competition_registration_id',
            'competition_idea_id',
            'id',
            'id'
        );
    }
}
