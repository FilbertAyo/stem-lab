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
    ];

    protected $casts = [
        'student_count_snapshot' => 'integer',
        'girls_count_snapshot' => 'integer',
        'girls_ratio_snapshot' => 'decimal:2',
        'eligibility_met' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

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
