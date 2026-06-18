<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProgressSummary extends Model
{
    protected $fillable = [
        'student_id',
        'user_id',
        'teacher_club_id',
        'models_attempted',
        'models_completed',
        'vr_attempts',
        'vr_passed',
        'avg_vr_score',
        'resources_accessed',
        'projects_viewed',
        'last_activity_at',
        'attention_flags',
        'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'avg_vr_score' => 'decimal:2',
            'last_activity_at' => 'datetime',
            'attention_flags' => 'array',
            'computed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function teacherClub(): BelongsTo
    {
        return $this->belongsTo(TeacherClub::class);
    }

    public function hasAttentionFlag(string $flag): bool
    {
        return in_array($flag, $this->attention_flags ?? [], true);
    }

    public function needsAttention(): bool
    {
        $flags = $this->attention_flags ?? [];

        return ! empty(array_diff($flags, ['not_registered']));
    }
}
