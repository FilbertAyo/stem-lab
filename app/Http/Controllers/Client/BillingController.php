<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $user->refresh();

        $recentOrders = $user->subscriptionOrders()
            ->with('package')
            ->latest('id')
            ->limit(5)
            ->get();

        return view('client.back.billing.index', [
            'user' => $user,
            'recentOrders' => $recentOrders,
            'showPaywall' => $request->boolean('paywall') && ! $user->hasPlatformAccess(),
        ]);
    }

    public function transactions(Request $request): View
    {
        $orders = $request->user()
            ->subscriptionOrders()
            ->with('package')
            ->latest('id')
            ->paginate(20);

        return view('client.back.billing.transactions', [
            'orders' => $orders,
        ]);
    }
}
