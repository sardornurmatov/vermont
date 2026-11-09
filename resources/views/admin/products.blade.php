@extends('layouts.app')

@section('title', __('Admin · Mahsulotlar'))

@section('content')
<section class="page-shell admin-page-shell">
    <div class="admin-workspace">
    @include('admin._navigation')
    <div class="admin-main-content">
    <p class="eyebrow">VERMONTSHOP · {{ __('ADMIN') }}</p>

    <div class="section-heading">
        <h1 class="page-title">{{ __('Mahsulotlar') }}</h1>
        <a class="button button-outline" href="{{ route('admin.orders.index') }}">
            {{ __('Buyurtmalarga o‘tish') }}
        </a>
    </div>

    <details class="admin-order" id="payment-settings">
        <summary>{{ __('To‘lov kartasi ma’lumotlari') }}</summary>
        <form class="product-admin-form" method="post" action="{{ route('admin.settings.payment') }}">
            @csrf
            @method('PUT')
            <input name="card_number" value="{{ old('card_number', $cardNumber) }}" placeholder="{{ __('Karta raqami') }}" required>
            <input name="card_holder" value="{{ old('card_holder', $cardHolder) }}" placeholder="{{ __('Karta egasi') }}" required>
            <button class="button button-dark" type="submit">{{ __('Karta ma’lumotlarini saqlash') }}</button>
        </form>
    </details>

    <details class="admin-order" id="categories">
        <summary>{{ __('Kategoriyalar') }}</summary>
        <form class="product-admin-form" method="post" action="{{ route('admin.categories.store') }}">
            @csrf
            <input name="name" value="{{ old('name') }}" placeholder="{{ __('Yangi kategoriya nomi') }}" maxlength="80" required>
            <button class="button button-dark" type="submit">{{ __('Kategoriyani saqlash') }}</button>
        </form>
        <p class="muted-note">
            {{ __('Saqlangan kategoriyalar:') }}
            @forelse ($categories as $category)
                <span>{{ $category->translatedName() }}@unless($loop->last), @endunless</span>
            @empty
                {{ __('Hozircha kategoriya yo‘q') }}
            @endforelse
        </p>
    </details>

    <details class="admin-order" id="add-product" open>
        <summary>{{ __('Yangi mahsulot qo‘shish') }}</summary>
        <form class="product-admin-form" method="post" enctype="multipart/form-data" action="{{ route('admin.products.store') }}">
            @csrf
            {{-- Yangi mahsulotda hali $product mavjud emas, shuning uchun null uzatiladi. --}}
            @include('admin.admin_product-form-fields', ['product' => null])
            <button class="button button-dark" type="submit">{{ __('Mahsulot qo‘shish') }}</button>
        </form>
    </details>

    </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-translate-products]').forEach((button) => {
    button.addEventListener('click', async () => {
        const form = button.closest('form');
        const status = form?.querySelector('.translation-status');
        const name = form?.querySelector('[name="name"]')?.value.trim();
        const description = form?.querySelector('[name="description"]')?.value.trim() || '';

        if (!name) {
            if (status) status.textContent = @json(__('Avval mahsulot nomini kiriting.'));
            return;
        }

        button.disabled = true;
        if (status) status.textContent = @json(__('Tarjima qilinmoqda…'));

        try {
            const response = await fetch(button.dataset.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ name, description })
            });

            const result = await response.json();
            if (!response.ok) throw new Error(result.message || @json(__('Tarjima bajarilmadi.')));

            Object.entries(result).forEach(([field, value]) => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) input.value = value;
            });

            if (status) status.textContent = @json(__('Tarjimalar tayyor. Saqlash uchun formani yuboring.'));
        } catch (error) {
            if (status) status.textContent = error.message;
        } finally {
            button.disabled = false;
        }
    });
});
</script>
@endpush
