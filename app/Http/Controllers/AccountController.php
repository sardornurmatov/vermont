<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function dashboard(Request $request)
    {
        $orders = $request->user()->orders();
        $stats = [
            'total' => (clone $orders)->count(),
            'awaiting' => (clone $orders)->whereIn('status', ['awaiting_payment', 'payment_review', 'payment_rejected'])->count(),
            'inProgress' => (clone $orders)->whereIn('status', ['payment_confirmed', 'preparing', 'delivering'])->count(),
            'completed' => (clone $orders)->where('status', 'completed')->count(),
        ];
        $latestOrders = (clone $orders)->latest()->limit(5)->get();

        return view('account.dashboard', compact('stats', 'latestOrders'));
    }

    public function orders(Request $request)
    {
        $orders=$request->user()->orders()->with('items')->latest()->paginate(10);
        return view('account.orders',compact('orders'));
    }
}
