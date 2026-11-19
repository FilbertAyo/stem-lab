<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\SubscriptionPackage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $packages = SubscriptionPackage::query()->active()->ordered()->get()->keyBy('slug');

        $publicSchoolClub = Club::query()
            ->active()
            ->where('name', 'like', '%Chomoza%')
            ->ordered()
            ->first()
            ?? Club::query()->active()->ordered()->first();

        return view('client.back.billing.index', [
            'user' => $user,
            'packages' => $packages,
            'monthlyPackage' => $packages->get('individual'),
            'yearlyPackage' => $packages->get('individual-yearly'),
            'publicSchoolClub' => $publicSchoolClub,
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
