<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorWeeklyStat extends Model
{
    protected $table = 'visitor_weekly_stats';

    protected $fillable = [
        'visitor_id',
        'week_start',
        'views',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
        ];
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }
}
