@extends('layouts.app')

@section('title', __('Admin dashboard').' — Vermontshop')

@section('content')
@php
    $statusLabels = [
        'awaiting_payment' => __('To‘lov kutilmoqda'),
        'payment_review' => __('Chek tekshirilmoqda'),
        'payment_confirmed' => __('To‘lov tasdiqlandi'),
        'payment_rejected' => __('To‘lov rad etildi'),
        'preparing' => __('Tayyorlanmoqda'),
        'delivering' => __('Yetkazilmoqda'),
        'completed' => __('Yakunlandi'),
    ];
@endphp

<section class="page-shell admin-dashboard-shell">
    <div class="admin-workspace">
    @include('admin._navigation')
    <div class="admin-main-content">
    <div class="dashboard-welcome">
        <div>
            <p class="eyebrow">VERMONTSHOP · {{ __('BOSHQARUV') }}</p>
            <h1 class="page-title">{{ __('Xush kelibsiz') }}, {{ auth()->user()->name }}</h1>
            <p class="dashboard-lead">{{ __('Do‘koningizning bugungi holati va muhim ko‘rsatkichlari.') }}</p>
        </div>
        <a class="button button-dark" href="{{ route('admin.products.index') }}">{{ __('Mahsulot qo‘shish') }} <span aria-hidden="true">＋</span></a>
    </div>

    <div class="dashboard-metrics">
        <article class="metric-card metric-primary">
            <span class="metric-label">{{ __('Bugungi tushum') }}</span>
            <strong>{{ number_format($stats['todayRevenue'], 0, ',', ' ') }} <small>{{ __('so‘m') }}</small></strong>
            <span class="metric-foot">{{ __('Tasdiqlangan to‘lovlar') }}</span>
            <span class="metric-icon" aria-hidden="true">↗</span>
        </article>
        <article class="metric-card">
            <span class="metric-label">{{ __('Shu oy tushumi') }}</span>
            <strong>{{ number_format($stats['monthRevenue'], 0, ',', ' ') }} <small>{{ __('so‘m') }}</small></strong>
            <span class="metric-foot">{{ __('Joriy oy bo‘yicha') }}</span>
            <span class="metric-icon" aria-hidden="true">◷</span>
        </article>
        <a class="metric-card metric-card-link {{ $stats['receiptsToReview'] ? 'metric-attention' : '' }}" href="{{ route('admin.orders.index') }}">
            <span class="metric-label">{{ __('Tekshiriladigan cheklar') }}</span>
            <strong>{{ number_format($stats['receiptsToReview']) }}</strong>
            <span class="metric-foot">{{ __('Admin tasdig‘ini kutmoqda') }}</span>
            <span class="metric-icon" aria-hidden="true">◉</span>
        </a>
        <article class="metric-card">
            <span class="metric-label">{{ __('Jami buyurtmalar') }}</span>
            <strong>{{ number_format($stats['orders']) }}</strong>
            <span class="metric-foot">{{ __('Barcha davr bo‘yicha') }}</span>
            <span class="metric-icon" aria-hidden="true">▤</span>
        </article>
    </div>

    <div class="dashboard-content-grid">
        <section class="dashboard-panel sales-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <p class="eyebrow">{{ __('SAVDO TAHLILI') }}</p>
                    <h2>{{ __('Oxirgi 7 kun tushumi') }}</h2>
                </div>
                <span class="panel-period">{{ __('So‘nggi hafta') }}</span>
            </div>
            <div class="sales-chart" role="img" aria-label="{{ __('Oxirgi 7 kunlik tushum grafigi') }}">
                @foreach($sales as $day)
                    @php($barHeight = $day['amount'] > 0 ? max(8, (int) round($day['amount'] / $maxSales * 100)) : 3)
                    <div class="chart-column" title="{{ $day['date'] }} · {{ number_format($day['amount'], 0, ',', ' ') }} {{ __('so‘m') }}">
                        <span class="chart-value">{{ $day['amount'] > 0 ? number_format($day['amount'], 0, ',', ' ') : '–' }}</span>
                        <div class="chart-track"><span class="chart-bar" style="height: {{ $barHeight }}%"></span></div>
                        <span class="chart-day">{{ $day['label'] }}<small>{{ $day['date'] }}</small></span>
                    </div>
                @endforeach
            </div>
            <p class="chart-caption">{{ __('Grafik tasdiqlangan va bajarilayotgan buyurtmalar tushumini ko‘rsatadi.') }}</p>
        </section>

        <aside class="dashboard-panel store-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <p class="eyebrow">{{ __('DO‘KON HOLATI') }}</p>
                    <h2>{{ __('Asosiy ko‘rsatkichlar') }}</h2>
                </div>
                <span class="store-status-dot" title="{{ __('Faol') }}"></span>
            </div>
            <div class="store-stat-list">
                <div><span>{{ __('Mijozlar') }}</span><strong>{{ number_format($stats['customers']) }}</strong></div>
                <div><span>{{ __('Faol mahsulotlar') }}</span><strong>{{ number_format($stats['products']) }}</strong></div>
                <div><span>{{ __('Kategoriyalar') }}</span><strong>{{ number_format($stats['categories']) }}</strong></div>
                <div class="{{ $stats['lowStock'] ? 'stock-warning' : '' }}"><span>{{ __('Kam qolgan mahsulotlar') }}</span><strong>{{ number_format($stats['lowStock']) }}</strong></div>
            </div>
            <a class="panel-link" href="{{ route('admin.products.index') }}">{{ __('Mahsulotlarni boshqarish') }} <span aria-hidden="true">→</span></a>
        </aside>

        <section class="dashboard-panel latest-orders-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <p class="eyebrow">{{ __('SO‘NGGI FAOLLIK') }}</p>
                    <h2>{{ __('Oxirgi buyurtmalar') }}</h2>
                </div>
                <a class="panel-link" href="{{ route('admin.orders.index') }}">{{ __('Barchasini ko‘rish') }} <span aria-hidden="true">→</span></a>
            </div>
            <div class="dashboard-table-wrap">
                <table class="dashboard-table">
                    <thead>
                        <tr><th>{{ __('Buyurtma') }}</th><th>{{ __('Mijoz') }}</th><th>{{ __('Sana') }}</th><th>{{ __('Holati') }}</th><th>{{ __('Jami') }}</th></tr>
                    </thead>
                    <tbody>
                        @forelse($latestOrders as $order)
                            <tr>
                                <td><strong>#{{ $order->order_number }}</strong></td>
                                <td>{{ $order->user?->name ?? '—' }}</td>
                                <td>{{ $order->created_at->format('d.m.Y') }}</td>
                                <td><span class="status-pill status-{{ $order->status }}">{{ $statusLabels[$order->status] ?? $order->status }}</span></td>
                                <td class="table-total">{{ number_format($order->total, 0, ',', ' ') }} {{ __('so‘m') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="dashboard-empty">{{ __('Hozircha buyurtmalar yo‘q.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
    </div>
    </div>
</section>
@endsection
