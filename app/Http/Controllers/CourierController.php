<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\TelegramOrderNotifier;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->deliveries()->with(['user', 'items'])->latest()->paginate(20);
        return view('courier.orders', compact('orders'));
    }

    public function update(Request $request, Order $order, TelegramOrderNotifier $telegram)
    {
        abort_unless($order->courier_id === $request->user()->id, 403);
        $data = $request->validate([
            'delivery_status' => 'required|in:picked_up,on_the_way,delivered,delivery_failed',
        ]);

        $allowedNext = [
            'assigned' => ['picked_up'],
            'picked_up' => ['on_the_way'],
            'on_the_way' => ['delivered', 'delivery_failed'],
            'delivery_failed' => ['on_the_way'],
        ];
        abort_unless(in_array($data['delivery_status'], $allowedNext[$order->delivery_status] ?? []), 422, __('delivery.ui.validation_sequence'));

        $order->update(['delivery_status' => $data['delivery_status']]);
        if ($data['delivery_status'] === 'delivered') {
            $order->update(['status' => 'completed']);
        }
        $telegram->sendDeliveryUpdate($order, $data['delivery_status']);

        return back()->with('status', __('delivery.ui.update_success'));
    }
}
