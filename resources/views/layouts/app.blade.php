<!doctype html>
<html lang="{{ app()->getLocale() }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Vermontshop')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/delivery.css') }}">
</head>
<body id="top">
<div class="announcement">{{ __('Yangi mavsum kolleksiyasi · Toshkent bo‘ylab tez yetkazib berish') }}</div>
<header class="site-header">
    <a class="brand" href="{{ route('shop.index') }}"><img class="brand-image" src="{{ asset('images/vermont-logo.png') }}" alt="Vermont Shop"></a>
    <nav class="main-nav" aria-label="{{ __('Asosiy menyu') }}">
        <a href="{{ route('shop.women') }}">{{ __('Ayollar') }}</a>
        <a href="{{ route('shop.men') }}">{{ __('Erkaklar') }}</a>
        <a href="{{ route('shop.accessories') }}">{{ __('Aksessuarlar') }}</a>
    </nav>
    <div class="header-actions">
        <form class="search-form" method="get" action="{{ route('shop.index') }}" role="search">
            <span aria-hidden="true">⌕</span>
            <input name="q" placeholder="{{ __('Mahsulot izlash...') }}" value="{{ request('q') }}" aria-label="{{ __('Mahsulot izlash...') }}">
        </form>
        <select class="locale-select" aria-label="{{ __('Til') }}" onchange="location.href='/til/'+this.value">
            <option value="uz" @selected(app()->getLocale()==='uz')>UZ</option>
            <option value="ru" @selected(app()->getLocale()==='ru')>RU</option>
            <option value="en" @selected(app()->getLocale()==='en')>EN</option>
        </select>
        <button class="icon-button" id="theme-toggle" type="button" aria-label="{{ __('Rang rejimini o‘zgartirish') }}">◐</button>
        @auth
            @if(auth()->user()->role==='admin')
                <a class="account-link" href="{{ route('admin.products.index') }}">{{ __('Admin') }}</a>
            @elseif(auth()->user()->role==='courier')
                <a class="account-link" href="{{ route('courier.orders.index') }}">{{ __('delivery.ui.courier_panel') }}</a>
            @else
                <a class="account-link" href="{{ route('account.orders') }}">{{ __('Buyurtmalarim') }}</a>
            @endif
            <form method="post" action="{{ route('logout') }}">@csrf<button class="text-button">{{ __('Chiqish') }}</button></form>
        @else
            <a class="account-link" href="{{ route('login') }}">{{ __('Kirish') }}</a>
        @endauth
        <a class="bag-link" href="{{ route('cart.index') }}">{{ __('Savat') }} <span>{{ collect(session('cart',[]))->sum('quantity') }}</span></a>
    </div>
</header>
@if(session('status'))<div class="flash success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="flash error">{{ $errors->first() }}</div>@endif
<main>@yield('content')</main>
<footer class="site-footer">
    <div class="footer-main">
        <div class="footer-about">
            <a class="brand footer-brand" href="{{ route('shop.index') }}"><img class="brand-image" src="{{ asset('images/vermont-logo.png') }}" alt="Vermont Shop"></a>
            <p>{{ __('Har kuni kiyishga arziydigan uslub.') }}</p>
            <p class="footer-location">{{ __('O‘zbekiston bo‘ylab yetkazib beramiz') }}</p>
        </div>
        <div class="footer-column"><h3>{{ __('Kolleksiya') }}</h3><a href="{{ route('shop.women') }}">{{ __('Ayollar') }}</a><a href="{{ route('shop.men') }}">{{ __('Erkaklar') }}</a><a href="{{ route('shop.accessories') }}">{{ __('Aksessuarlar') }}</a><a href="{{ route('shop.index') }}">{{ __('Barcha mahsulotlar') }}</a></div>
        <div class="footer-column"><h3>{{ __('Mijozlar uchun') }}</h3><a href="{{ route('account.orders') }}">{{ __('Buyurtmalarim') }}</a><a href="{{ route('cart.index') }}">{{ __('Savat') }}</a><a href="{{ route('login') }}">{{ __('Hisobga kirish') }}</a></div>
        <div class="footer-column"><h3>{{ __('Biz bilan bog‘laning') }}</h3><p>{{ __('Savollaringiz bo‘lsa, Telegram orqali yozing.') }}</p><a class="footer-telegram" href="{{ config('services.telegram.channel_url','#') }}" target="_blank" rel="noopener">{{ __('Telegram kanalimiz') }} ↗</a></div>
    </div>
    <div class="footer-bottom"><span>© {{ date('Y') }} Vermontshop</span><span>{{ __('Tanlangan kiyimlar va aksessuarlar') }}</span><a href="#top">{{ __('Yuqoriga qaytish') }} ↑</a></div>
</footer>
<script src="{{ asset('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
