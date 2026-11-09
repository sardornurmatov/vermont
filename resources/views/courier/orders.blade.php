@extends('layouts.app')
@section('title', __('delivery.ui.courier_orders').' — Vermontshop')
@section('content')
@php($nextSteps = ['assigned'=>['picked_up'], 'picked_up'=>['on_the_way'], 'on_the_way'=>['delivered','delivery_failed'], 'delivery_failed'=>['on_the_way']])
<section class="page-shell"><p class="eyebrow">VERMONTSHOP · {{ __('delivery.ui.courier') }}</p><h1 class="page-title">{{ __('delivery.ui.courier_title') }}</h1><div class="admin-orders">
@forelse($orders as $order)<article class="admin-order courier-order"><div class="admin-order-head"><div><strong>#{{ $order->order_number }}</strong><span>{{ $order->user->name }} · {{ $order->phone }}</span></div><b>{{ __('delivery.status.'.$order->delivery_status) }}</b></div><p><strong>{{ __('delivery.ui.address') }}:</strong> {{ $order->address }}</p>@if($order->delivery_note)<p><strong>{{ __('delivery.ui.note') }}:</strong> {{ $order->delivery_note }}</p>@endif @if($order->estimated_delivery_at)<p>{{ __('delivery.ui.eta') }}: {{ $order->estimated_delivery_at->format('d.m.Y H:i') }}</p>@endif
@foreach($order->items as $item)<small>{{ $item->product_name }} × {{ $item->quantity }}</small>@endforeach
@if($steps = ($nextSteps[$order->delivery_status] ?? []))<form class="courier-update-form" method="post" action="{{ route('courier.orders.update',$order) }}">@csrf @method('PATCH')<label>{{ __('delivery.ui.courier_step') }}<select name="delivery_status" required>@foreach($steps as $step)<option value="{{ $step }}">{{ __('delivery.status.'.$step) }}</option>@endforeach</select></label><button class="button button-dark">{{ __('delivery.ui.save_status') }}</button></form>@else<p class="muted-note">{{ __('delivery.ui.finished') }}</p>@endif
</article>@empty<div class="empty-state">{{ __('delivery.ui.no_orders') }}</div>@endforelse</div><div class="pagination">{{ $orders->links() }}</div></section>
@endsection
