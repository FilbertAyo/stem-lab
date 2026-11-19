<?php

namespace App\Services;

use App\Models\SubscriptionOrder;
use App\Models\SubscriptionPackage;
use App\Models\User;
use App\Services\Snippe\SnippeClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SubscriptionCheckoutService
{
    public function __construct(
        private readonly SnippeClient $snippeClient,
        private readonly SubscriptionSelectionService $selectionService,
    ) {}

    public function startIndividualCheckout(User $user): SubscriptionOrder
    {
        if (! $this->selectionService->paymentsEnabled()) {
            abort(403, 'Online subscription is not available yet.');
        }

        if ($user->hasLockedSubscriptionPlan()) {
            abort(403, 'You already have an active plan through your STEM club.');
        }

        $package = SubscriptionPackage::query()
            ->active()
            ->where('slug', 'individual')
            ->firstOrFail();

        if (! $package->requires_payment || (int) $package->price_amount_tzs < 500) {
            throw new RuntimeException('Individual package is not configured for Snippe checkout.');
        }

        return DB::transaction(function () use ($user, $package): SubscriptionOrder {
            $existing = SubscriptionOrder::query()
                ->where('user_id', $user->id)
                ->where('package_slug', 'individual')
                ->open()
                ->where(function ($query): void {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if ($existing && filled($existing->checkout_url)) {
                return $existing;
            }

            if ($existing) {
                $order = $existing;
            } else {
                $order = SubscriptionOrder::query()->create([
                    'user_id' => $user->id,
                    'subscription_package_id' => $package->id,
                    'package_slug' => $package->slug,
                    'status' => SubscriptionOrder::STATUS_PENDING,
                    'amount_tzs' => (int) $package->price_amount_tzs,
                    'currency' => (string) config('snippe.currency', 'TZS'),
                    'period_days' => max(1, (int) $package->billing_period_days),
                    'idempotency_key' => $this->makeIdempotencyKey(),
                    'metadata' => [
                        'user_id' => $user->id,
                        'package_slug' => $package->slug,
                    ],
                ]);
            }

            $session = $this->snippeClient->createSession(
                $this->buildSessionPayload($user, $package, $order),
                $order->idempotency_key,
            );

            $order->forceFill([
                'status' => SubscriptionOrder::STATUS_CHECKOUT_CREATED,
                'snippe_session_reference' => $session['reference'] ?? $order->snippe_session_reference,
                'checkout_url' => $session['checkout_url'] ?? null,
                'expires_at' => isset($session['expires_at'])
                    ? \Illuminate\Support\Carbon::parse($session['expires_at'])
                    : now()->addSeconds((int) config('snippe.session_expires_in', 3600)),
                'metadata' => array_merge($order->metadata ?? [], [
                    'snippe_session' => [
                        'reference' => $session['reference'] ?? null,
                        'short_code' => $session['short_code'] ?? null,
                    ],
                ]),
            ])->save();

            if (! filled($order->checkout_url)) {
                throw new RuntimeException('Snippe did not return a checkout URL.');
            }

            return $order->fresh() ?? $order;
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSessionPayload(User $user, SubscriptionPackage $package, SubscriptionOrder $order): array
    {
        $payload = [
            'amount' => (int) $order->amount_tzs,
            'currency' => $order->currency,
            'allowed_methods' => ['mobile_money'],
            'description' => sprintf('%s — %d day access', $package->name, $order->period_days),
            'redirect_url' => route('client.subscription-packages.success', $order),
            'webhook_url' => route('webhooks.snippe'),
            'expires_in' => (int) config('snippe.session_expires_in', 3600),
            'metadata' => [
                'order_ulid' => (string) $order->ulid,
                'user_id' => (string) $user->id,
                'package_slug' => $package->slug,
            ],
            'line_items' => [
                [
                    'id' => $package->slug,
                    'name' => $package->name,
                    'description' => sprintf('%d days prepaid access', $order->period_days),
                    'quantity' => 1,
                    'unit_price' => (int) $order->amount_tzs,
                ],
            ],
            'display' => [
                'show_line_items' => true,
                'show_description' => true,
                'button_text' => 'Pay with mobile money',
                'success_message' => 'Payment successful! Your subscription will activate shortly.',
            ],
            'customer' => array_filter([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ]),
        ];

        $profileId = config('snippe.profile_id');
        if (is_string($profileId) && $profileId !== '') {
            $payload['profile_id'] = $profileId;
        }

        return $payload;
    }

    private function makeIdempotencyKey(): string
    {
        // Snippe idempotency keys must be 30 characters or fewer.
        return Str::lower(Str::random(30));
    }
}
