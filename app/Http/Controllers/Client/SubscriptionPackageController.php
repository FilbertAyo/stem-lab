<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionOrder;
use App\Services\Snippe\SnippeClient;
use App\Services\SubscriptionCheckoutService;
use App\Services\SubscriptionFulfillmentService;
use App\Services\SubscriptionSelectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class SubscriptionPackageController extends Controller
{
    public function select(
        Request $request,
        SubscriptionSelectionService $selectionService,
        SubscriptionCheckoutService $checkoutService,
    ): RedirectResponse {
        if (! $selectionService->paymentsEnabled()) {
            return redirect()
                ->to(url()->previous().'#platform-subscription')
                ->with('error', 'Online subscription is not available yet. Please check back soon.');
        }

        $validated = $request->validate([
            'package' => [
                'required',
                'string',
                Rule::in(['free-trial', 'individual']),
            ],
        ]);

        $user = $request->user();

        if ($user->hasLockedSubscriptionPlan()) {
            return redirect()
                ->to(url()->previous().'#platform-subscription')
                ->with('error', 'You already have an active plan through your STEM club.');
        }

        if ($validated['package'] === 'individual') {
            try {
                $order = $checkoutService->startIndividualCheckout($user);
            } catch (Throwable $exception) {
                report($exception);

                return redirect()
                    ->to(url()->previous().'#platform-subscription')
                    ->with('error', 'We could not start checkout right now. Please try again shortly.');
            }

            return redirect()->away($order->checkout_url);
        }

        try {
            $package = $selectionService->selectBySlug($user, $validated['package']);
        } catch (Throwable $exception) {
            return redirect()
                ->to(url()->previous().'#platform-subscription')
                ->with('error', $exception->getMessage() ?: 'Unable to activate that package.');
        }

        return redirect()
            ->to(url()->previous().'#platform-subscription')
            ->with('status', "You are now on the {$package->name} plan.");
    }

    public function success(
        Request $request,
        SubscriptionOrder $subscriptionOrder,
        SnippeClient $snippeClient,
        SubscriptionFulfillmentService $fulfillmentService,
    ): View|RedirectResponse {
        abort_unless($subscriptionOrder->user_id === $request->user()->id, 403);

        if (! $subscriptionOrder->isFulfilled() && filled($subscriptionOrder->snippe_session_reference)) {
            try {
                $session = $snippeClient->getSession($subscriptionOrder->snippe_session_reference);
                if (($session['status'] ?? null) === 'completed' && ! $subscriptionOrder->isFulfilled()) {
                    $fulfilled = $fulfillmentService->fulfillPaidOrder(
                        $subscriptionOrder,
                        isset($session['payment_reference']) ? (string) $session['payment_reference'] : null,
                    );
                    $fulfillmentService->notifyFulfillment($fulfilled);
                    $subscriptionOrder = $fulfilled;
                }
            } catch (RuntimeException) {
                // Webhook remains source of truth; success page can still show pending state.
            }
        }

        return view('client.back.subscription-success', [
            'order' => $subscriptionOrder->fresh(['package', 'user']),
        ]);
    }

    public function cancel(Request $request, SubscriptionOrder $subscriptionOrder): RedirectResponse
    {
        abort_unless($subscriptionOrder->user_id === $request->user()->id, 403);

        if ($subscriptionOrder->isOpen()) {
            $subscriptionOrder->forceFill([
                'status' => SubscriptionOrder::STATUS_CANCELLED,
            ])->save();
        }

        return redirect()
            ->to(route('dashboard').'#platform-subscription')
            ->with('warning', 'Checkout was cancelled. You can try again whenever you are ready.');
    }
}
