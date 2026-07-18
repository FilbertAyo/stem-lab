<?php

namespace App\Models\Concerns;

use Illuminate\Database\QueryException;
use Illuminate\Support\Str;

trait HasPublicShortId
{
    public bool $resolvedViaLegacyRouteKey = false;

    /**
     * Reserved six-character route segments that must never be assigned as public IDs.
     *
     * @var list<string>
     */
    protected static array $reservedPublicIds = [
        'create',
        'export',
        'delete',
        'update',
        'search',
        'import',
        'status',
        'result',
        'report',
    ];

    protected static function bootHasPublicShortId(): void
    {
        static::creating(function ($model): void {
            if (blank($model->public_id)) {
                $model->public_id = static::generateUniquePublicId();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return filled($this->public_id) ? 'public_id' : 'ulid';
    }

    public function getRouteKey(): mixed
    {
        return filled($this->public_id)
            ? $this->public_id
            : $this->ulid;
    }

    public function markResolvedViaLegacyRouteKey(bool $legacy = true): static
    {
        $this->resolvedViaLegacyRouteKey = $legacy;

        return $this;
    }

    public function wasResolvedViaLegacyRouteKey(): bool
    {
        return $this->resolvedViaLegacyRouteKey;
    }

    public static function generateUniquePublicId(int $maxAttempts = 25): string
    {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $candidate = static::generatePublicIdCandidate();

            if (! static::query()->where('public_id', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Unable to generate a unique public_id after '.$maxAttempts.' attempts.');
    }

    public static function generatePublicIdCandidate(): string
    {
        do {
            $candidate = Str::lower(Str::random(6));
        } while (
            ! preg_match('/[a-z]/', $candidate)
            || in_array($candidate, static::$reservedPublicIds, true)
        );

        return $candidate;
    }

    public function assignPublicIdIfMissing(): bool
    {
        if (filled($this->public_id)) {
            return false;
        }

        for ($attempt = 0; $attempt < 25; $attempt++) {
            $this->public_id = static::generatePublicIdCandidate();

            try {
                $this->saveQuietly();

                return true;
            } catch (QueryException $exception) {
                if (! $this->isUniquePublicIdViolation($exception)) {
                    throw $exception;
                }

                $this->public_id = null;
            }
        }

        throw new \RuntimeException('Unable to assign a unique public_id for model '.$this->getKey().'.');
    }

    protected function isUniquePublicIdViolation(QueryException $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'public_id')
            || str_contains($message, 'unique');
    }
}
