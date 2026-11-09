<aside class="account-sidebar" aria-label="{{ __('Mijoz kabineti') }}">
    <div class="account-sidebar-heading">
        <span class="account-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
        <span><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span>
    </div>
    <p class="account-sidebar-label">{{ __('SHAXSIY KABINET') }}</p>
    <nav class="account-nav">
        <a class="{{ request()->routeIs('account.dashboard') ? 'is-active' : '' }}" href="{{ route('account.dashboard') }}"><span aria-hidden="true">⌂</span>{{ __('Umumiy ko‘rinish') }}</a>
        <a class="{{ request()->routeIs('account.orders') ? 'is-active' : '' }}" href="{{ route('account.orders') }}"><span aria-hidden="true">▤</span>{{ __('Buyurtmalarim') }}</a>
        <a href="{{ route('shop.index') }}"><span aria-hidden="true">↗</span>{{ __('Xaridni davom ettirish') }}</a>
    </nav>
</aside>
