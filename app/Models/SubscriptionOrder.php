<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionOrder extends Model
{
    use HasPublicUlid;

    public const STATUS_PENDING = 'pending';

    public const STATUS_CHECKOUT_CREATED = 'checkout_created';

    public const STATUS_PAID = 'paid';

    public const STATUS_FULFILLED = 'fulfilled';

    public const STATUS_FAILED = 'failed';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'subscription_package_id',
        'package_slug',
        'status',
        'amount_tzs',
        'currency',
        'period_days',
        'snippe_session_reference',
        'snippe_payment_reference',
        'checkout_url',
        'idempotency_key',
        'metadata',
        'paid_at',
        'expires_at',
        'fulfilled_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_tzs' => 'integer',
            'period_days' => 'integer',
            'metadata' => 'array',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'fulfilled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPackage::class, 'subscription_package_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_CHECKOUT_CREATED,
        ]);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CHECKOUT_CREATED,
        ], true);
    }

    public function isFulfilled(): bool
    {
        return $this->status === self::STATUS_FULFILLED;
    }
}
