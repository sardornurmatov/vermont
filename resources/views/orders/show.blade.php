@extends('layouts.app')
@section('title', __('Buyurtma').' '.$order->order_number.' — Vermontshop')
@section('content')
@php
    $statusLabels = ['awaiting_payment'=>'To‘lov kutilmoqda','payment_review'=>'Chek tekshirilmoqda','payment_rejected'=>'Chek qayta yuborilsin','payment_confirmed'=>'To‘lov tasdiqlandi','preparing'=>'Tayyorlanmoqda','delivering'=>'Yetkazilmoqda','completed'=>'Yakunlandi'];
    $deliverySteps = ['waiting_payment', 'preparing', 'assigned', 'picked_up', 'on_the_way', 'delivered'];
    $deliveryIndex = array_search($order->delivery_status, $deliverySteps, true);
@endphp
<section class="page-shell narrow-shell"><p class="eyebrow">{{ __('BUYURTMA') }} #{{ $order->order_number }}</p><h1 class="page-title">{{ __('delivery.ui.order_details') }}</h1>

<div class="delivery-tracking"><div class="delivery-heading"><div><span class="eyebrow">{{ __('delivery.ui.delivery_tracking') }}</span><h2>{{ __('delivery.status.'.$order->delivery_status) }}</h2></div>@if($order->estimated_delivery_at)<span class="delivery-eta">{{ __('delivery.ui.eta') }}<strong>{{ $order->estimated_delivery_at->format('d.m.Y H:i') }}</strong></span>@endif</div>
@if($order->delivery_status === 'delivery_failed')<p class="delivery-alert">{{ __('delivery.failed_notice') }}</p>@else
<ol class="delivery-timeline">@foreach($deliverySteps as $index=>$step)<li class="{{ $deliveryIndex !== false && $index <= $deliveryIndex ? 'is-done' : '' }}"><span class="timeline-dot"></span><span>{{ __('delivery.status.'.$step) }}</span></li>@endforeach</ol>@endif
@if($order->courier)<div class="courier-contact"><span>{{ __('delivery.ui.courier') }}: <strong>{{ $order->courier->name }}</strong></span><span>{{ __('delivery.ui.courier_contact_note') }}</span></div>@else<p class="muted-note">{{ __('delivery.ui.waiting_assignment') }}</p>@endif
<p class="delivery-address"><strong>{{ __('delivery.ui.address') }}:</strong> {{ $order->address }}@if($order->delivery_note)<br><span>{{ __('delivery.ui.note') }}: {{ $order->delivery_note }}</span>@endif</p></div>

<h2 class="subheading">{{ __('delivery.ui.payment_details') }}</h2><p>{{ __('Quyidagi kartaga buyurtma summasini o‘tkazing. Keyin to‘lov chekini yuklang.') }}</p>
<div class="payment-card"><span class="eyebrow">{{ __('KARTA RAQAMI') }}</span><strong>{{ $cardNumber }}</strong><button type="button" class="copy-card" data-copy="{{ preg_replace('/\s+/','',$cardNumber) }}" data-copied="{{ __('Nusxalandi ✓') }}">{{ __('Raqamni nusxalash') }}</button><small>{{ __('Karta egasi:') }} {{ $cardHolder }}</small></div>
<div class="order-summary receipt-summary"><p>{{ __('Buyurtma') }} <span>#{{ $order->order_number }}</span></p><p>{{ __('To‘lov holati') }} <span>{{ __($statusLabels[$order->status] ?? $order->status) }}</span></p><p>{{ __('delivery.ui.products') }} <span>{{ number_format($order->total - $order->delivery_fee,0,',',' ') }} {{ __('so‘m') }}</span></p><p>{{ __('delivery.ui.delivery_fee') }} <span>{{ $order->delivery_fee ? number_format($order->delivery_fee,0,',',' ').' '.__('so‘m') : __('delivery.ui.free_or_unset') }}</span></p><p class="summary-total">{{ __('To‘lov summasi') }} <span>{{ number_format($order->total,0,',',' ') }} {{ __('so‘m') }}</span></p></div>
@if(in_array($order->status,['awaiting_payment','payment_rejected']))<form class="upload-form" method="post" enctype="multipart/form-data" action="{{ route('orders.receipt',$order) }}">@csrf<label>{{ __('Chek rasmi (JPG, PNG yoki WEBP; 5 MB gacha)') }}</label><input class="auth-input" type="file" name="receipt" accept="image/jpeg,image/png,image/webp" required><button class="button button-dark full-button">{{ __('Chekni yuborish') }}</button></form>@elseif($order->receipt_path)<p class="muted-note">{{ __('Chek yuborilgan va admin tekshiryapti.') }}</p>@endif
<h2 class="subheading">{{ __('delivery.ui.products') }}</h2>@foreach($order->items as $item)<p class="order-item-line">{{ $item->product_name }} · {{ $item->variant }} <span>{{ $item->quantity }} × {{ number_format($item->price,0,',',' ') }} {{ __('so‘m') }}</span></p>@endforeach
</section>
@endsection
