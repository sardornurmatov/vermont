@extends('layouts.app')

@section('title', __('Mahsulotlarni tahrirlash').' — Vermontshop')

@section('content')
<section class="page-shell admin-page-shell">
    <div class="admin-workspace">
        @include('admin._navigation')
        <div class="admin-main-content">
            <p class="eyebrow">VERMONTSHOP · {{ __('ADMIN') }}</p>
            <div class="section-heading">
                <h1 class="page-title">{{ __('Mahsulotlarni tahrirlash') }}</h1>
                <a class="button button-dark" href="{{ route('admin.products.index') }}#add-product">{{ __('Yangi mahsulot qo‘shish') }} <span aria-hidden="true">＋</span></a>
            </div>

            <div class="admin-orders">
                @forelse ($products as $product)
                    <article class="admin-order">
                        <div class="admin-order-head">
                            <div>
                                <strong>{{ $product->translated('name') }}</strong>
                                <span>
                                    {{ $product->gender === 'women' ? __('Ayollar') : __('Erkaklar') }}
                                    · {{ $product->translated('category') }}
                                    · {{ __('Omborda') }}: {{ $product->stock }}
                                </span>
                            </div>
                            <b>{{ number_format($product->price, 0, ',', ' ') }} {{ __('so‘m') }}</b>
                        </div>

                        <details>
                            <summary>{{ __('Mahsulotni tahrirlash') }}</summary>
                            <form class="product-admin-form" method="post" enctype="multipart/form-data" action="{{ route('admin.products.update', $product) }}">
                                @csrf
                                @method('PATCH')
                                @include('admin.admin_product-form-fields', ['product' => $product])
                                <button class="button button-dark" type="submit">{{ __('Saqlash') }}</button>
                            </form>
                        </details>

                        <form method="post" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('{{ __('Mahsulotni vitrindan yashiraymi?') }}')" class="product-hide-form">
                            @csrf
                            @method('DELETE')
                            <button class="text-button" type="submit">{{ __('Do‘kondan yashirish') }}</button>
                        </form>
                    </article>
                @empty
                    <div class="empty-state">{{ __('Hozircha mahsulotlar mavjud emas.') }}</div>
                @endforelse
            </div>

            <div class="pagination">{{ $products->links() }}</div>
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
