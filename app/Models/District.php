<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_id',
        'name',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function groupedNamesByRegionId(): array
    {
        return static::query()
            ->orderBy('name')
            ->get(['region_id', 'name'])
            ->groupBy(fn (self $district) => (string) $district->region_id)
            ->map(fn ($items) => $items->pluck('name')->values()->all())
            ->all();
    }
}
