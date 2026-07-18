<?php

namespace App\Models;

use App\Models\Concerns\HasPublicShortId;
use App\Models\Concerns\HasPublicUlid;
use App\Models\Concerns\ScopedToManySchoolClasses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Request;

class Model3d extends Model
{
    use HasFactory;
    use HasPublicUlid;
    use HasPublicShortId {
        HasPublicShortId::getRouteKeyName insteadof HasPublicUlid;
    }
    use ScopedToManySchoolClasses;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject_id',
        'name',
        'file',
        'embed_url',
        'thumbnail',
        'description',
        'tags',
        'is_active',
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
     * Get the subject that owns the 3D model.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get all questions for this 3D model.
     */
    public function questions()
    {
        return $this->hasMany(ModelQuestion::class, 'model_id')->orderBy('order');
    }

    /**
     * Get all answers for this 3D model.
     */
    public function answers()
    {
        return $this->hasMany(ModelQuestionAnswer::class, 'model_id');
    }

    /**
     * Scope a query to only include models for a specific subject.
     */
    public function scopeForSubject(Builder $query, int $subjectId): Builder
    {
        return $query->where('subject_id', $subjectId);
    }

    /**
     * Scope a query to search models by name or description.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to include the subject relationship.
     */
    public function scopeWithSubject(Builder $query): Builder
    {
        return $query->with('subject');
    }

    /**
     * Scope a query to order models by name.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    /**
     * Scope a query to only include active models.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function hasGlb(): bool
    {
        return filled($this->file);
    }

    public function hasEmbed(): bool
    {
        return filled($this->embed_url);
    }

    public function hasViewer(): bool
    {
        return $this->hasGlb() || $this->hasEmbed();
    }

    public function defaultViewerMode(): string
    {
        return $this->hasGlb() ? 'glb' : 'embed';
    }

    public function viewerEmbedUrl(): ?string
    {
        return \App\Support\Model3dEmbed::viewerUrl($this->embed_url, $this->name);
    }

    public function isSketchfabEmbed(): bool
    {
        return \App\Support\Model3dEmbed::isSketchfabUrl($this->embed_url);
    }

    /**
     * Get the weekly sessions associated with this 3D model.
     */
    public function weeklySessions()
    {
        return $this->belongsToMany(WeeklySession::class, 'weekly_session_model3ds')
            ->withTimestamps();
    }

    /**
     * Retrieve the model for route model binding.
     * For client routes, only allow active models.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Check if this is an admin route by checking the request path
        $isAdminRoute = Request::is('admin/*');

        if (!$isAdminRoute) {
            // For client routes, only allow active models
            return $this->where($field ?? $this->getRouteKeyName(), $value)
                ->where('is_active', true)
                ->first();
        }

        // For admin routes, allow all models
        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }
}
