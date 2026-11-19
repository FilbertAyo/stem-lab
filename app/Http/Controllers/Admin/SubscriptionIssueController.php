<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionOrder;
use App\Models\User;
use Illuminate\View\View;

class SubscriptionIssueController extends Controller
{
    public function index(): View
    {
        $problemOrders = SubscriptionOrder::query()
            ->with(['user', 'package'])
            ->whereIn('status', [
                SubscriptionOrder::STATUS_FAILED,
                SubscriptionOrder::STATUS_EXPIRED,
                SubscriptionOrder::STATUS_PENDING,
                SubscriptionOrder::STATUS_CHECKOUT_CREATED,
            ])
            ->latest('id')
            ->limit(50)
            ->get();

        $expiredUsers = User::query()
            ->whereIn('role', ['teacher', 'student'])
            ->whereIn('subscription_tier', ['trial', 'individual', 'pro'])
            ->whereNotNull('subscription_expires_at')
            ->where('subscription_expires_at', '<=', now())
            ->orderByDesc('subscription_expires_at')
            ->limit(50)
            ->get();

        $openCheckoutCount = SubscriptionOrder::query()->open()->count();
        $failedCount = SubscriptionOrder::query()
            ->whereIn('status', [
                SubscriptionOrder::STATUS_FAILED,
                SubscriptionOrder::STATUS_EXPIRED,
            ])
            ->count();

        return view('admin.subscription-issues.index', [
            'problemOrders' => $problemOrders,
            'expiredUsers' => $expiredUsers,
            'openCheckoutCount' => $openCheckoutCount,
            'failedCount' => $failedCount,
            'expiredUserCount' => $expiredUsers->count(),
        ]);
    }
}
