@extends('layouts.app')
@section('title', __('Savat').' — Vermontshop')
@section('content')
<section class="page-shell"><p class="eyebrow">{{ __('XARID SAVATI') }}</p><h1 class="page-title">{{ __('Savat') }}</h1>
@if($lines->isEmpty())<div class="empty-state large-empty">{{ __('Savating hozircha bo‘sh.') }}<p><a class="button button-dark" href="{{ route('shop.index') }}">{{ __('Xaridni boshlash') }}</a></p></div>
@else
<form method="post" action="{{ route('cart.update') }}">@csrf @method('PATCH')<div class="cart-layout"><div class="cart-lines">@foreach($lines as $id=>$line)<div class="cart-line"><img src="{{ $line['product']->image_url }}" alt="{{ $line['product']->translated('name') }}"><div class="cart-line-info"><a href="{{ route('shop.show',$line['product']->slug) }}">{{ $line['product']->translated('name') }}</a><small>{{ $line['variant'] }}</small><strong>{{ number_format($line['product']->price,0,',',' ') }} {{ __('so‘m') }}</strong></div><label class="cart-quantity">{{ __('Soni') }}<input name="items[{{ $id }}][quantity]" type="number" min="0" max="10" value="{{ $line['quantity'] }}"></label></div>@endforeach</div><aside class="order-summary"><h2>{{ __('Buyurtma jami') }}</h2><p>{{ __('Mahsulotlar') }} <span>{{ number_format($total,0,',',' ') }} {{ __('so‘m') }}</span></p><p>{{ __('Yetkazib berish') }} <span>{{ __('Kelishiladi') }}</span></p><hr><p class="summary-total">{{ __('Jami') }} <span>{{ number_format($total,0,',',' ') }} {{ __('so‘m') }}</span></p><button class="button button-outline full-button">{{ __('Savatni yangilash') }}</button><a class="button button-dark full-button" href="{{ route('checkout') }}">{{ __('Buyurtmani rasmiylashtirish') }}</a></aside></div></form>
@endif
</section>
@endsection
