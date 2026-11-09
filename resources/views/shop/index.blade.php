@extends('layouts.app')
@section('title', __('Vermontshop — yangi mavsum uslubi'))
@section('content')
<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">{{ __('YANGI MAVSUM · 2026') }}</p>
        <h1>{{ __('O‘zingga mos') }}<br><em>{{ __('uslubni top.') }}</em></h1>
        <p class="hero-lead">{{ __('Har kungi garderob uchun tanlangan kiyimlar va mayda detallar.') }}</p>
        <div class="hero-buttons"><a class="button button-dark" href="#products">{{ __('Kolleksiyani ko‘r') }} <span>↗</span></a><a class="quiet-link" href="{{ route('shop.women') }}">{{ __('Ayollar uchun') }}</a></div>
        <div class="hero-note"><span class="avatars">✦ ✦ ✦</span><span>{{ __('O‘z uslubini topganlar uchun') }}</span></div>
    </div>
    <div class="hero-image"><img src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=90" alt="{{ __('Vermontshop yangi mavsum modasi') }}"><div class="image-caption"><span>{{ __('THE AUTUMN EDIT') }}</span><span>01 / 03</span></div><span class="hero-stamp">VERMONT<br>EST. 2026</span></div>
</section>
<section class="benefit-strip"><span>✳ {{ __('Tanlab olingan kolleksiya') }}</span><span>✳ {{ __('Qulay qaytarish') }}</span><span>✳ {{ __('O‘zbekiston bo‘ylab yetkazish') }}</span><span>✳ {{ __('Har kuni yangi uslub') }}</span></section>
<section class="collection-section" id="products">
    <div class="section-heading"><div><p class="eyebrow">{{ $catalogEyebrow }}</p><h2>{{ $catalogTitle }}</h2></div><span class="quiet-link">{{ __('Mahsulotlarni qidirish va saralash') }}</span></div>
    @include('shop._catalog-controls')
    @include('shop._product-grid')
</section>
<section class="editorial"><div class="editorial-image"><img src="https://images.unsplash.com/photo-1485230895905-ec40ba36b9bc?auto=format&fit=crop&w=1000&q=85" alt="{{ __('Kundalik uslub') }}"></div><div class="editorial-copy"><p class="eyebrow">{{ __('VERMONTSHOP’DA') }}</p><h2>{{ __('Oddiy tanlovlar.') }}<br><em>{{ __('O‘ziga xos kayfiyat.') }}</em></h2><p>{{ __('O‘zingni erkin his qiladigan kiyimlar va obrazni yakunlaydigan aksessuarlar.') }}</p><a class="button button-outline" href="{{ route('shop.index') }}#products">{{ __('Kolleksiyani ko‘r') }}</a></div></section>
@endsection
