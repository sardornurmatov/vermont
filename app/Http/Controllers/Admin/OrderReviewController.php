<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\TelegramOrderNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderReviewController extends Controller
{
    public function index()
    {
        $orders = Order::with(['user', 'items', 'courier'])->latest()->paginate(20);
        $couriers = User::where('role', 'courier')->orderBy('name')->get(['id', 'name', 'email']);
        return view('admin.orders', compact('orders', 'couriers'));
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:payment_confirmed,preparing,delivering,completed,payment_rejected',
            'admin_note' => 'nullable|string|max:500',
        ]);
        $order->update($data);

        if ($data['status'] === 'payment_confirmed' && $order->delivery_status === 'waiting_payment') {
            $order->update(['delivery_status' => 'preparing']);
        }

        return back()->with('status', __('Buyurtma holati yangilandi.'));
    }

    public function assignDelivery(Request $request, Order $order, TelegramOrderNotifier $telegram)
    {
        abort_unless(in_array($order->status, ['payment_confirmed', 'preparing', 'delivering', 'completed']), 422, __('delivery.ui.payment_first'));

        $data = $request->validate([
            'courier_email' => 'required|email|exists:users,email',
            'estimated_delivery_at' => 'nullable|date|after:now',
        ]);
        $courier = User::where('email', $data['courier_email'])->firstOrFail();
        abort_if($courier->role === 'admin', 422, __('delivery.ui.admin_not_courier'));

        if ($courier->role !== 'courier') {
            $courier->update(['role' => 'courier']);
        }

        $order->update([
            'courier_id' => $courier->id,
            'delivery_status' => 'assigned',
            'estimated_delivery_at' => $data['estimated_delivery_at'] ?? null,
        ]);
        $telegram->sendDeliveryUpdate($order, 'Kuryer biriktirildi');

        return back()->with('status', __('delivery.ui.assigned_success'));
    }

    public function receipt(Order $order)
    {
        abort_unless($order->receipt_path, 404);
        return Storage::disk('local')->download($order->receipt_path);
    }
}
