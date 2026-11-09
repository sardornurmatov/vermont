<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $paidStatuses = ['payment_confirmed', 'preparing', 'delivering', 'completed'];

        $stats = [
            'orders' => Order::count(),
            'customers' => User::where('role', 'customer')->count(),
            'products' => Product::where('active', true)->count(),
            'categories' => Category::count(),
            'receiptsToReview' => Order::where('status', 'payment_review')->count(),
            'lowStock' => Product::where('active', true)->where('stock', '<=', 5)->count(),
            'todayRevenue' => Order::whereIn('status', $paidStatuses)->whereDate('created_at', today())->sum('total'),
            'monthRevenue' => Order::whereIn('status', $paidStatuses)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total'),
        ];

        $sales = collect(range(6, 0))->map(function (int $daysAgo) use ($paidStatuses) {
            $date = today()->subDays($daysAgo);

            return [
                'label' => Carbon::parse($date)->locale(app()->getLocale())->isoFormat('dd'),
                'date' => $date->format('d.m'),
                'amount' => (int) Order::whereIn('status', $paidStatuses)
                    ->whereDate('created_at', $date)
                    ->sum('total'),
            ];
        });

        $maxSales = max(1, (int) $sales->max('amount'));
        $latestOrders = Order::with('user')->latest()->limit(6)->get();

        return view('admin.dashboard', compact('stats', 'sales', 'maxSales', 'latestOrders'));
    }
}
