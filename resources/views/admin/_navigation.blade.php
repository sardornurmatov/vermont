<aside class="admin-sidebar" aria-label="{{ __('Admin bo‘limlari') }}">
    <div class="admin-sidebar-brand">
        <span class="admin-sidebar-mark" aria-hidden="true">V</span>
        <span><strong>Vermontshop</strong><small>{{ __('Boshqaruv paneli') }}</small></span>
    </div>
    <p class="admin-sidebar-label">{{ __('ASOSIY MENYU') }}</p>
    <nav class="admin-nav">
        <a class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}"><span aria-hidden="true">⌂</span>{{ __('Dashboard') }}</a>
        <a class="{{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}" href="{{ route('admin.orders.index') }}"><span aria-hidden="true">▤</span>{{ __('Buyurtmalar') }}</a>
        <a class="{{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}" href="{{ route('admin.products.index') }}"><span aria-hidden="true">◈</span>{{ __('Mahsulotlar') }}</a>
        <a href="{{ route('admin.products.index') }}#add-product"><span aria-hidden="true">＋</span>{{ __('Mahsulot qo‘shish') }}</a>
        @if(request()->routeIs('admin.products.*'))
            <a href="{{ route('admin.products.index') }}#categories"><span aria-hidden="true">⌑</span>{{ __('Kategoriyalar') }}</a>
            <a href="{{ route('admin.products.index') }}#payment-settings"><span aria-hidden="true">▣</span>{{ __('To‘lov sozlamalari') }}</a>
            <a class="{{ request()->routeIs('admin.products.edit-list') ? 'is-active' : '' }}" href="{{ route('admin.products.edit-list') }}"><span aria-hidden="true">✎</span>{{ __('Mahsulotlarni tahrirlash') }}</a>
        @endif
    </nav>
    <div class="admin-sidebar-bottom">
        <a class="admin-store-link" href="{{ route('shop.index') }}"><span aria-hidden="true">↗</span>{{ __('Do‘konni ko‘rish') }}</a>
    </div>
</aside>
