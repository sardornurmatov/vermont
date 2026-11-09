<div class="product-grid">
@forelse($products as $product)
    <article class="product-card">
        <a class="product-photo" href="{{ route('shop.show',$product->slug) }}"><img loading="lazy" src="{{ $product->image_url }}" alt="{{ $product->translated('name') }}">@if($product->old_price)<span class="badge">{{ __('CHEGIRMA') }}</span>@endif<span class="quick-view">{{ __('Ko‘rish') }} ↗</span></a>
        <div class="product-meta"><div><p class="product-category">{{ $product->translated('category') }} · {{ $product->gender==='women' ? __('Ayollar') : __('Erkaklar') }}</p><a class="product-name" href="{{ route('shop.show',$product->slug) }}">{{ $product->translated('name') }}</a><div class="product-rating" aria-label="{{ __('Sharhlar') }}">@if($product->reviews_count)<span>★ {{ number_format((float) $product->reviews_avg_rating, 1) }}</span><small>({{ $product->reviews_count }})</small>@else<span>{{ __('Hali sharh yo‘q') }}</span>@endif</div></div><p class="price">{{ number_format($product->price,0,',',' ') }} <small>{{ __('so‘m') }}</small></p></div>
        @if($product->old_price)<p class="old-price">{{ number_format($product->old_price,0,',',' ') }} {{ __('so‘m') }}</p>@endif
    </article>
@empty
    <div class="empty-state">{{ __('Mos mahsulot topilmadi. Filtrlarni o‘zgartirib ko‘ring.') }}</div>
@endforelse
</div>
<div class="pagination">{{ $products->links() }}</div>
