<?php

namespace App\Models;

use App\Enums\LearningEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StudentLearningEvent extends Model
{
    protected $fillable = [
        'user_id',
        'student_id',
        'event_type',
        'subject_type',
        'subject_id',
        'metadata',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => LearningEventType::class,
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
