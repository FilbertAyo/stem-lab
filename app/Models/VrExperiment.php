<?php

namespace App\Models;

use App\Models\Concerns\HasPublicShortId;
use App\Models\Concerns\HasPublicUlid;
use App\Models\Concerns\TracksOwnership;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VrExperiment extends Model
{
    use HasFactory;
    use HasPublicShortId {
        HasPublicShortId::getRouteKeyName insteadof HasPublicUlid;
    }
    use HasPublicUlid;
    use TracksOwnership;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'external_url',
        'thumbnail',
        'subject_id',
        'is_active',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the subject that owns the VR experiment.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the school classes that can access this experiment.
     */
    public function schoolClasses(): BelongsToMany
    {
        return $this->belongsToMany(
            SchoolClass::class,
            'vr_experiment_school_class',
            'vr_experiment_id',
            'school_class_id'
        )->withTimestamps();
    }

    /**
     * Get all question banks for this experiment.
     */
    public function questionBanks(): HasMany
    {
        return $this->hasMany(VrQuestionBank::class);
    }

    /**
     * Limit results to content for the student's class.
     * When no class is set on the student, no filter is applied.
     */
    public function scopeVisibleToStudentClass(Builder $query, ?int $schoolClassId): Builder
    {
        if ($schoolClassId === null) {
            return $query;
        }

        return $query->where(function (Builder $classQuery) use ($schoolClassId) {
            $classQuery->whereDoesntHave('schoolClasses')
                ->orWhereHas('schoolClasses', fn (Builder $relation) => $relation->where('school_classes.id', $schoolClassId));
        });
    }

    public function isAccessibleToStudentClass(?int $schoolClassId): bool
    {
        if ($schoolClassId === null) {
            return true;
        }

        if (! $this->relationLoaded('schoolClasses')) {
            $this->load('schoolClasses');
        }

        if ($this->schoolClasses->isEmpty()) {
            return true;
        }

        return $this->schoolClasses->contains('id', $schoolClassId);
    }

    /**
     * Scope a query to only include active experiments.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to include the subject relationship.
     */
    public function scopeWithSubject(Builder $query): Builder
    {
        return $query->with('subject');
    }

    /**
     * Scope a query to filter by subject.
     */
    public function scopeForSubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope a query to search experiments by name or description.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to order experiments by name.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    /**
     * Get the weekly sessions associated with this VR experiment.
     */
    public function weeklySessions(): HasMany
    {
        return $this->hasMany(WeeklySession::class);
    }
}
