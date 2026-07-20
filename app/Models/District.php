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

    /**
     * District options keyed by region id for cascading selects.
     *
     * @return array<string, list<array{id: int, name: string}>>
     */
    public static function groupedByRegionId(): array
    {
        return static::query()
            ->orderBy('name')
            ->get(['id', 'region_id', 'name'])
            ->groupBy(fn (self $district) => (string) $district->region_id)
            ->map(fn ($items) => $items->map(fn (self $district) => [
                'id' => $district->id,
                'name' => $district->name,
            ])->values()->all())
            ->all();
    }

    public static function findIdForRegionAndName(?int $regionId, ?string $name): ?int
    {
        if ($regionId === null || ! filled($name)) {
            return null;
        }

        $normalized = trim($name);

        $district = static::query()
            ->where('region_id', $regionId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($normalized)])
            ->first();

        return $district?->id;
    }
}
