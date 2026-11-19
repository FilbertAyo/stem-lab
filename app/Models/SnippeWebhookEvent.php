<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SnippeWebhookEvent extends Model
{
    protected $fillable = [
        'event_id',
        'event_type',
        'payment_reference',
        'session_reference',
        'subscription_order_id',
        'processing_status',
        'payload',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function subscriptionOrder(): BelongsTo
    {
        return $this->belongsTo(SubscriptionOrder::class);
    }
}
