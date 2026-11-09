<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\TelegramOrderNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
        if (empty($request->session()->get('cart', []))) {
            return redirect()->route('cart.index');
        }

        $deliveryFee = (int) config('services.delivery.fee', 0);
        $subtotal = collect($request->session()->get('cart', []))->sum(function ($line, $id) {
            $product = Product::find($id);
            return $product ? $product->price * (int) ($line['quantity'] ?? 0) : 0;
        });

        return view('orders.checkout', compact('deliveryFee', 'subtotal'));
    }

    public function store(Request $request, TelegramOrderNotifier $telegram)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:32',
            'address' => 'required|string|max:1000',
            'delivery_note' => 'nullable|string|max:500',
        ]);
        $cart = $request->session()->get('cart', []);
        if (!$cart) return redirect()->route('cart.index');

        $order = DB::transaction(function () use ($cart, $data, $request) {
            $products = Product::whereIn('id', array_keys($cart))->lockForUpdate()->get()->keyBy('id');
            abort_if($products->count() !== count($cart), 422, __('Mahsulot topilmadi.'));

            $subtotal = 0;
            foreach ($cart as $id => $line) {
                $product = $products->get($id);
                $quantity = (int) ($line['quantity'] ?? 0);
                abort_if(!$product || !$product->active || $quantity < 1 || $product->stock < $quantity, 422, __('Mahsulot omborda yetarli emas.'));
                $subtotal += $product->price * $quantity;
            }

            $deliveryFee = max(0, (int) config('services.delivery.fee', 0));
            $order = Order::create([
                'order_number' => 'V'.now()->format('ymd').str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT),
                'user_id' => $request->user()->id,
                'phone' => $data['phone'],
                'address' => $data['address'],
                'delivery_note' => $data['delivery_note'] ?? null,
                'delivery_fee' => $deliveryFee,
                'delivery_status' => 'waiting_payment',
                'total' => $subtotal + $deliveryFee,
            ]);

            foreach ($cart as $id => $line) {
                $product = $products->get($id);
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->translated('name'),
                    'variant' => $line['variant'] ?? null,
                    'price' => $product->price,
                    'quantity' => (int) $line['quantity'],
                ]);
            }

            return $order;
        });

        $request->session()->forget('cart');
        $telegram->send($order);
        return redirect()->route('orders.show', $order)->with('status', __('Buyurtma yaratildi. To‘lov qilib, chekni yuklang.'));
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id || $request->user()->role === 'admin', 403);
        $order->load(['items', 'courier']);
        $cardNumber = StoreSetting::get('payment_card_number', config('services.payments.card_number'));
        $cardHolder = StoreSetting::get('payment_card_holder', config('services.payments.card_holder'));
        return view('orders.show', compact('order', 'cardNumber', 'cardHolder'));
    }

    public function receipt(Request $request, Order $order, TelegramOrderNotifier $telegram)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless(in_array($order->status, ['awaiting_payment', 'payment_rejected']), 403);
        $data = $request->validate(['receipt' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);
        $path = $data['receipt']->store('receipts', 'local');
        $order->update(['receipt_path' => $path, 'status' => 'payment_review']);
        $telegram->send($order);
        return back()->with('status', __('Chek yuborildi. Admin to‘lovni tekshiryapti.'));
    }
}
