<?php

namespace App\Services;

use App\Mail\SubscriptionPaymentSucceededMail;
use App\Models\SubscriptionOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SubscriptionFulfillmentService
{
    public function __construct(
        private readonly AdminActivityNotifier $adminActivityNotifier,
    ) {}

    public function fulfillPaidOrder(SubscriptionOrder $order, ?string $paymentReference = null): SubscriptionOrder
    {
        return DB::transaction(function () use ($order, $paymentReference): SubscriptionOrder {
            /** @var SubscriptionOrder $locked */
            $locked = SubscriptionOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->isFulfilled()) {
                return $locked;
            }

            /** @var User $user */
            $user = User::query()
                ->whereKey($locked->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $periodDays = max(1, (int) $locked->period_days);
            $startsAt = now();

            if (
                $user->subscription_expires_at
                && $user->subscription_expires_at->isFuture()
                && in_array($user->subscription_tier, ['individual', 'pro', 'trial'], true)
            ) {
                $startsAt = $user->subscription_expires_at->copy();
            }

            $expiresAt = $startsAt->copy()->addDays($periodDays);

            $user->forceFill([
                'subscription_tier' => 'individual',
                'subscription_expires_at' => $expiresAt,
                'subscription_renewal_reminded_at' => null,
            ])->save();

            $locked->forceFill([
                'status' => SubscriptionOrder::STATUS_FULFILLED,
                'snippe_payment_reference' => $paymentReference ?: $locked->snippe_payment_reference,
                'paid_at' => $locked->paid_at ?? now(),
                'fulfilled_at' => now(),
            ])->save();

            return $locked->fresh(['user', 'package']) ?? $locked;
        });
    }

    public function notifyFulfillment(SubscriptionOrder $order): void
    {
        $order->loadMissing('user', 'package');
        $user = $order->user;

        if ($user?->email) {
            try {
                Mail::to($user->email)->send(new SubscriptionPaymentSucceededMail($order));
            } catch (Throwable) {
                // Payment access must not fail because mail delivery failed.
            }
        }

        try {
            $this->adminActivityNotifier->alert(
                subject: 'Subscription payment received',
                headline: 'A prepaid Individual subscription was activated',
                summary: sprintf(
                    '%s paid %s %s for %s (%s days).',
                    $user?->name ?? 'A user',
                    number_format((int) $order->amount_tzs),
                    $order->currency,
                    $order->package?->name ?? $order->package_slug,
                    $order->period_days,
                ),
                details: [
                    ['label' => 'User', 'value' => $user?->email ?: (string) $user?->id],
                    ['label' => 'Order', 'value' => (string) $order->ulid],
                    ['label' => 'Payment ref', 'value' => (string) ($order->snippe_payment_reference ?: '—')],
                    ['label' => 'Expires', 'value' => optional($user?->subscription_expires_at)->toDateTimeString() ?: '—'],
                ],
                categories: ['needs_attention', 'technical'],
            );
        } catch (Throwable) {
            // Ignore admin alert failures.
        }
    }
}
