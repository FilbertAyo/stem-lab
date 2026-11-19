<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = SubscriptionOrder::query()
            ->with(['user', 'package'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.subscription-orders.index', compact('orders'));
    }

    public function show(SubscriptionOrder $subscriptionOrder): View
    {
        $subscriptionOrder->load(['user', 'package']);

        return view('admin.subscription-orders.show', [
            'order' => $subscriptionOrder,
        ]);
    }
}
