@extends('layouts.app')

@section('title', __('Shaxsiy kabinet').' — Vermontshop')

@section('content')
@php
    $statusLabels = [
        'awaiting_payment' => __('To‘lov kutilmoqda'),
        'payment_review' => __('Chek tekshirilmoqda'),
        'payment_rejected' => __('Chek qayta yuborilsin'),
        'payment_confirmed' => __('To‘lov tasdiqlandi'),
        'preparing' => __('Tayyorlanmoqda'),
        'delivering' => __('Yetkazilmoqda'),
        'completed' => __('Yakunlandi'),
    ];
@endphp

<section class="page-shell account-page-shell">
    <div class="account-workspace">
        @include('account._navigation')
        <div class="account-main">
            <header class="account-welcome">
                <div>
                    <p class="eyebrow">VERMONTSHOP · {{ __('SHAXSIY KABINET') }}</p>
                    <h1>{{ __('Xush kelibsiz') }}, {{ auth()->user()->name }}</h1>
                    <p>{{ __('Buyurtmalaringiz va ularning holatini shu yerdan kuzatib boring.') }}</p>
                </div>
                <a class="button button-dark" href="{{ route('shop.index') }}">{{ __('Xaridni davom ettirish') }} <span aria-hidden="true">↗</span></a>
            </header>

            <div class="account-metrics">
                <article class="account-metric account-metric-highlight">
                    <span>{{ __('Jami buyurtmalar') }}</span>
                    <strong>{{ number_format($stats['total']) }}</strong>
                    <small>{{ __('Barcha xaridlaringiz') }}</small>
                    <i aria-hidden="true">▤</i>
                </article>
                <article class="account-metric">
                    <span>{{ __('To‘lov bosqichida') }}</span>
                    <strong>{{ number_format($stats['awaiting']) }}</strong>
                    <small>{{ __('To‘lov yoki chek tasdig‘i kutilmoqda') }}</small>
                    <i aria-hidden="true">◷</i>
                </article>
                <article class="account-metric">
                    <span>{{ __('Jarayondagi buyurtmalar') }}</span>
                    <strong>{{ number_format($stats['inProgress']) }}</strong>
                    <small>{{ __('Tayyorlanmoqda yoki yetkazilmoqda') }}</small>
                    <i aria-hidden="true">↗</i>
                </article>
                <article class="account-metric">
                    <span>{{ __('Yakunlangan') }}</span>
                    <strong>{{ number_format($stats['completed']) }}</strong>
                    <small>{{ __('Yetkazib berilgan buyurtmalar') }}</small>
                    <i aria-hidden="true">✓</i>
                </article>
            </div>

            <section class="account-panel">
                <div class="account-panel-heading">
                    <div><p class="eyebrow">{{ __('SO‘NGGI FAOLLIK') }}</p><h2>{{ __('So‘nggi buyurtmalar') }}</h2></div>
                    <a class="panel-link" href="{{ route('account.orders') }}">{{ __('Barchasini ko‘rish') }} <span aria-hidden="true">→</span></a>
                </div>
                <div class="account-order-list">
                    @forelse($latestOrders as $order)
                        <a class="account-order-row" href="{{ route('orders.show', $order) }}">
                            <span class="account-order-icon" aria-hidden="true">▤</span>
                            <span class="account-order-info"><strong>#{{ $order->order_number }}</strong><small>{{ $order->created_at->format('d.m.Y · H:i') }}</small></span>
                            <span class="status-pill status-{{ $order->status }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                            <strong class="account-order-total">{{ number_format($order->total, 0, ',', ' ') }} {{ __('so‘m') }}</strong>
                            <span class="account-order-arrow" aria-hidden="true">→</span>
                        </a>
                    @empty
                        <div class="account-empty">
                            <span aria-hidden="true">⌑</span>
                            <strong>{{ __('Hozircha buyurtmalaringiz yo‘q.') }}</strong>
                            <p>{{ __('Do‘konimizdan o‘zingizga yoqqan mahsulotni tanlang.') }}</p>
                            <a class="button button-dark" href="{{ route('shop.index') }}">{{ __('Xaridni boshlash') }}</a>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="account-help-strip">
                <span class="account-help-icon" aria-hidden="true">?</span>
                <div><strong>{{ __('Buyurtma bo‘yicha savolingiz bormi?') }}</strong><small>{{ __('Buyurtma tafsilotlarini ochib, holatini va admin izohini ko‘ring.') }}</small></div>
                <a href="{{ route('account.orders') }}">{{ __('Buyurtmalarim') }} →</a>
            </section>
        </div>
    </div>
</section>
@endsection
