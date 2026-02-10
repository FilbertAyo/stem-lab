<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    protected $fillable = [
        'visitor_token',
        'first_seen_at',
        'last_seen_at',
        'landing_views',
        'converted_at',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    /**
     * Scope: visitors who have not registered.
     */
    public function scopeNotConverted($query)
    {
        return $query->whereNull('converted_at');
    }

    public function weeklyStats(): HasMany
    {
        return $this->hasMany(VisitorWeeklyStat::class);
    }
}
