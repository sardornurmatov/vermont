<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class TelegramOrderNotifier
{
    public function send(Order $order): void
    {
        $token = config('services.telegram.bot_token');
        $chat = config('services.telegram.chat_id');
        if (!$token || !$chat) return;
        $order->loadMissing(['user', 'items']);
        $items = $order->items->map(fn ($i) => "• {$i->product_name} ×{$i->quantity} — ".number_format($i->price * $i->quantity, 0, ',', ' ')).implode(" so‘m\n");
        $paymentStatus = $order->receipt_path ? 'Chek tekshirilmoqda' : 'To‘lov kutilmoqda';
        $deliveryNote = $order->delivery_note ? "\nIzoh: {$order->delivery_note}" : '';
        $text = "🛍 Yangi Vermontshop buyurtmasi\n#{$order->order_number}\n\nXaridor: {$order->user->name}\nTelefon: {$order->phone}\nManzil: {$order->address}{$deliveryNote}\n\n{$items}\nMahsulotlar: ".number_format($order->total - $order->delivery_fee, 0, ',', ' ')." so‘m\nYetkazib berish: ".number_format($order->delivery_fee, 0, ',', ' ')." so‘m\nJami: ".number_format($order->total, 0, ',', ' ' )." so‘m\nTo‘lov: karta o‘tkazmasi — {$paymentStatus}.";
        $base = 'https://api.telegram.org/bot'.$token.'/';
        try {
            Http::timeout(8)->post($base.'sendMessage', ['chat_id' => $chat, 'text' => $text]);
            if ($order->receipt_path && file_exists(storage_path('app/private/'.$order->receipt_path))) {
                Http::timeout(15)->attach('photo', file_get_contents(storage_path('app/private/'.$order->receipt_path)), basename($order->receipt_path))->post($base.'sendPhoto', ['chat_id' => $chat, 'caption' => "Chek — buyurtma #{$order->order_number}"]);
            }
        } catch (\Throwable $exception) {
            logger()->warning('Telegram notification could not be delivered.', ['order_id' => $order->id]);
        }
    }

    public function sendDeliveryUpdate(Order $order, string $status): void
    {
        $token = config('services.telegram.bot_token');
        $chat = config('services.telegram.chat_id');
        if (!$token || !$chat) return;
        $order->loadMissing('courier');
        $courier = $order->courier ? "\nKuryer: {$order->courier->name}" : '';
        $text = "🚚 Yetkazish yangilandi\n#{$order->order_number}\nHolat: {$status}{$courier}\nManzil: {$order->address}";
        try {
            Http::timeout(8)->post('https://api.telegram.org/bot'.$token.'/sendMessage', ['chat_id' => $chat, 'text' => $text]);
        } catch (\Throwable $exception) {
            logger()->warning('Telegram delivery update could not be delivered.', ['order_id' => $order->id]);
        }
    }
}
