<?php

namespace App\Services\Snippe;

use App\Models\SnippeWebhookEvent;
use App\Models\SubscriptionOrder;
use App\Services\SubscriptionFulfillmentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SnippeWebhookProcessor
{
    public function __construct(
        private readonly SubscriptionFulfillmentService $fulfillmentService,
    ) {}

    /**
     * @param  array<string, mixed>  $event
     */
    public function process(array $event): void
    {
        $eventId = (string) ($event['id'] ?? '');
        $eventType = (string) ($event['type'] ?? $event['event'] ?? '');

        if ($eventId === '' || $eventType === '') {
            Log::warning('Snippe webhook missing id or type.', ['event' => $event]);

            return;
        }

        if (SnippeWebhookEvent::query()->where('event_id', $eventId)->exists()) {
            return;
        }

        $data = is_array($event['data'] ?? null) ? $event['data'] : [];
        $paymentReference = isset($data['reference']) ? (string) $data['reference'] : null;
        $sessionReference = isset($data['session_reference'])
            ? (string) $data['session_reference']
            : null;

        $order = $this->resolveOrder($data, $sessionReference, $paymentReference);

        $webhookEvent = SnippeWebhookEvent::query()->create([
            'event_id' => $eventId,
            'event_type' => $eventType,
            'payment_reference' => $paymentReference,
            'session_reference' => $sessionReference,
            'subscription_order_id' => $order?->id,
            'processing_status' => 'received',
            'payload' => $event,
        ]);

        match ($eventType) {
            'payment.completed' => $this->handlePaymentCompleted($webhookEvent, $order, $data),
            'payment.failed' => $this->markOrderStatus($order, SubscriptionOrder::STATUS_FAILED),
            'payment.voided', 'payment.expired' => $this->markOrderStatus($order, SubscriptionOrder::STATUS_EXPIRED),
            default => null,
        };

        $webhookEvent->forceFill([
            'processing_status' => 'processed',
            'processed_at' => now(),
            'subscription_order_id' => $order?->fresh()?->id ?? $order?->id,
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function handlePaymentCompleted(
        SnippeWebhookEvent $webhookEvent,
        ?SubscriptionOrder $order,
        array $data,
    ): void {
        if (! $order) {
            Log::error('Snippe payment.completed could not be matched to an order.', [
                'event_id' => $webhookEvent->event_id,
                'data' => $data,
            ]);

            $webhookEvent->forceFill(['processing_status' => 'unmatched'])->save();

            return;
        }

        $expectedAmount = (int) $order->amount_tzs;
        $paidAmount = (int) data_get($data, 'amount.value', data_get($data, 'amount', 0));
        $currency = strtoupper((string) data_get($data, 'amount.currency', data_get($data, 'currency', 'TZS')));

        if ($paidAmount !== $expectedAmount || $currency !== strtoupper((string) $order->currency)) {
            Log::error('Snippe payment amount/currency mismatch.', [
                'order_id' => $order->id,
                'expected' => [$expectedAmount, $order->currency],
                'received' => [$paidAmount, $currency],
            ]);

            $webhookEvent->forceFill(['processing_status' => 'amount_mismatch'])->save();

            return;
        }

        $paymentReference = isset($data['reference']) ? (string) $data['reference'] : null;

        $fulfilled = $this->fulfillmentService->fulfillPaidOrder($order, $paymentReference);
        $this->fulfillmentService->notifyFulfillment($fulfilled);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveOrder(array $data, ?string $sessionReference, ?string $paymentReference): ?SubscriptionOrder
    {
        $orderUlid = data_get($data, 'metadata.order_ulid');

        if (is_string($orderUlid) && $orderUlid !== '') {
            $byUlid = SubscriptionOrder::query()->where('ulid', $orderUlid)->first();
            if ($byUlid) {
                return $byUlid;
            }
        }

        if ($sessionReference) {
            $bySession = SubscriptionOrder::query()
                ->where('snippe_session_reference', $sessionReference)
                ->first();
            if ($bySession) {
                return $bySession;
            }
        }

        if ($paymentReference) {
            return SubscriptionOrder::query()
                ->where('snippe_payment_reference', $paymentReference)
                ->first();
        }

        return null;
    }

    private function markOrderStatus(?SubscriptionOrder $order, string $status): void
    {
        if (! $order || $order->isFulfilled()) {
            return;
        }

        DB::transaction(function () use ($order, $status): void {
            /** @var SubscriptionOrder $locked */
            $locked = SubscriptionOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->isFulfilled() || ! $locked->isOpen()) {
                return;
            }

            $locked->forceFill(['status' => $status])->save();
        });
    }
}
