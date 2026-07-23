<?php

namespace App\Models;

use App\Models\Concerns\HasPublicShortId;
use App\Models\Concerns\HasPublicUlid;
use App\Models\Concerns\TracksOwnership;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class School extends Model
{
    use HasFactory;
    use TracksOwnership;
    use HasPublicUlid;
    use HasPublicShortId {
        HasPublicShortId::getRouteKeyName insteadof HasPublicUlid;
    }

    protected $fillable = [
        'ulid',
        'name',
        'latitude',
        'longitude',
        'phone',
        'email',
        'district',
        'district_id',
        'ward',
        'region_id',
        'region',
        'is_active',
        'admin_reviewed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_active' => 'boolean',
        'admin_reviewed_at' => 'datetime',
    ];

    /**
     * Get the region this school belongs to.
     */
    public function regionModel(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function districtModel(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function districtName(): ?string
    {
        return $this->districtModel?->name ?? $this->district;
    }

    /**
     * Get the teacher-club records for this school.
     */
    public function teacherClubs(): HasMany
    {
        return $this->hasMany(TeacherClub::class);
    }

    /**
     * Competition registrations linked to this school.
     */
    public function competitionRegistrations(): HasMany
    {
        return $this->hasMany(CompetitionRegistration::class);
    }

    /**
     * Get the students registered under this school (through teacher clubs).
     */
    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(Student::class, TeacherClub::class);
    }

    /**
     * Scope a query to only include active schools.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeNeedsReview(Builder $query): Builder
    {
        return $query->whereNull('admin_reviewed_at');
    }

    public function scopeReviewed(Builder $query): Builder
    {
        return $query->whereNotNull('admin_reviewed_at');
    }

    public function getNeedsReviewAttribute(): bool
    {
        return $this->admin_reviewed_at === null;
    }

    /**
     * Scope a query to order schools by name.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    /**
     * Pending review first, then newest registrations.
     */
    public function scopeReviewQueueOrder(Builder $query): Builder
    {
        return $query
            ->orderByRaw('admin_reviewed_at IS NULL DESC')
            ->orderByDesc('created_at');
    }
}
