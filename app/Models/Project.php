<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Request;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_category_id',
        'created_by',
        'name',
        'slug',
        'difficulty',
        'platform',
        'cover_image',
        'circuit_image',
        'description',
        'guide',
        'embedded_code',
        'is_published',
        'is_active',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tags()
    {
        return $this->belongsToMany(ProjectTag::class, 'project_tag')
            ->withTimestamps();
    }

    public function equipment()
    {
        return $this->belongsToMany(Equipment::class, 'equipment_project')
            ->withPivot(['quantity', 'notes'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    /**
     * Retrieve the project for route model binding.
     * On the admin subdomain: allow any project.
     * On the client (main) domain: only allow active + published projects.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $adminHost = parse_url(config('app.admin_url', 'https://admin.adilishastemlabs.com'), PHP_URL_HOST);
        $isAdminRequest = $adminHost && Request::getHost() === $adminHost;

        if (!$isAdminRequest) {
            return $this->where($field ?? $this->getRouteKeyName(), $value)
                ->where('is_active', true)
                ->where('is_published', true)
                ->first();
        }

        return $this->where($field ?? $this->getRouteKeyName(), $value)->first();
    }
}

