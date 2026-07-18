<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait TracksOwnership
{
    protected static function bootTracksOwnership(): void
    {
        static::creating(function ($model): void {
            $userId = Auth::id();

            if ($userId === null) {
                return;
            }

            if (blank($model->created_by)) {
                $model->created_by = $userId;
            }

            if (blank($model->updated_by)) {
                $model->updated_by = $userId;
            }
        });

        static::updating(function ($model): void {
            $userId = Auth::id();

            if ($userId === null) {
                return;
            }

            $model->updated_by = $userId;
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function ownershipName(?User $user): string
    {
        return $user?->name ?? 'Legacy / unavailable';
    }

    public function createdByName(): string
    {
        return $this->ownershipName($this->createdBy);
    }

    public function updatedByName(): string
    {
        return $this->ownershipName($this->updatedBy);
    }
}
