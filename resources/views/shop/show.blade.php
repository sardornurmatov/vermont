@extends('layouts.app')
@section('title', $product->translated('name').' — Vermontshop')
@section('content')
<div class="breadcrumbs"><a href="{{ route('shop.index') }}">{{ __('Bosh sahifa') }}</a> / {{ $product->translated('category') }} / {{ $product->translated('name') }}</div>
<section class="product-detail">
    <div class="detail-photo"><img src="{{ $product->image_url }}" alt="{{ $product->translated('name') }}"></div>
    <div class="detail-copy">
        <p class="eyebrow">{{ strtoupper($product->translated('category')) }} · {{ $product->gender==='women' ? __('AYOLLAR') : __('ERKAKLAR') }}</p>
        <h1>{{ $product->translated('name') }}</h1>
        <div class="detail-price">{{ number_format($product->price,0,',',' ') }} {{ __('so‘m') }} @if($product->old_price)<del>{{ number_format($product->old_price,0,',',' ') }} {{ __('so‘m') }}</del>@endif</div>
        <p>{{ $product->translated('description') }}</p>
        @if($product->reviews_count)<p class="detail-rating">★ {{ number_format((float) $product->reviews_avg_rating, 1) }} / 5 · {{ $product->reviews_count }} {{ __('sharh') }}</p>@else<p class="detail-rating">{{ __('Hali sharh yo‘q') }}</p>@endif
        <p class="stock-note">{{ $product->stock>0 ? __('Omborda mavjud') : __('Hozircha qolmagan') }}</p>
        <form method="post" action="{{ route('cart.add') }}">@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><label>{{ __('Rang va o‘lcham') }}</label><select name="variant" class="field-select">@foreach($product->colors ?? [] as $color)@foreach($product->sizes ?? [] as $size)<option>{{ __($color) }} / {{ __($size) }}</option>@endforeach @endforeach</select><label>{{ __('Miqdor') }}</label><input class="quantity-input" type="number" name="quantity" min="1" max="10" value="1"><button class="button button-dark full-button" {{ $product->stock<1?'disabled':'' }}>{{ __('Savatga qo‘shish') }}</button></form>
        <div class="detail-promises"><span>♡ {{ __('Qulay qaytarish') }}</span><span>↗ {{ __('O‘zbekiston bo‘ylab yetkazish') }}</span></div>
    </div>
</section>
<section class="review-section page-shell">
    <div class="section-heading"><div><p class="eyebrow">{{ __('MIJOZLAR FIKRI') }}</p><h2>{{ __('Sharhlar va baholar') }}</h2></div><span class="review-score">@if($product->reviews_count)★ {{ number_format((float) $product->reviews_avg_rating, 1) }} / 5 · {{ $product->reviews_count }}@else{{ __('Hali baholanmagan') }}@endif</span></div>
    @auth
        @php($myReview = $product->reviews->firstWhere('user_id', auth()->id()))
        <form class="review-form" method="post" action="{{ route('reviews.store', $product->slug) }}">@csrf
            <h3>{{ $myReview ? __('Sharhingizni yangilang') : __('Mahsulotga baho va sharh qoldiring') }}</h3>
            <label for="rating">{{ __('Baho') }}</label><select id="rating" name="rating" required><option value="">{{ __('Yulduz tanlang') }}</option>@for($star=5;$star>=1;$star--)<option value="{{ $star }}" @selected((int) old('rating', $myReview?->rating)===$star)>{{ $star }} ★</option>@endfor</select>
            <label for="comment">{{ __('Fikringiz') }}</label><textarea id="comment" name="comment" rows="4" minlength="5" maxlength="1500" required>{{ old('comment', $myReview?->comment) }}</textarea>
            <button class="button button-dark">{{ __('Sharhni yuborish') }}</button>
        </form>
    @else
        <p class="review-login-note">{{ __('Sharh qoldirish uchun hisobingizga kiring.') }} <a href="{{ route('login') }}">{{ __('Kirish') }}</a></p>
    @endauth
    <div class="review-list">
        @forelse($product->reviews as $review)
            <article class="review-card"><div class="review-card-head"><strong>{{ $review->user->name }}</strong><span>{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5-$review->rating) }}</span><time>{{ $review->created_at->format('d.m.Y') }}</time></div><p>{{ $review->comment }}</p></article>
        @empty
            <div class="empty-state">{{ __('Bu mahsulotga hali sharh qoldirilmagan.') }}</div>
        @endforelse
    </div>
</section>
<section class="collection-section"><div class="section-heading"><div><p class="eyebrow">{{ __('O‘XSHASH TANLOVLAR') }}</p><h2>{{ __('Senga yoqishi mumkin') }}</h2></div></div><div class="product-grid">@foreach($related as $item)<article class="product-card"><a class="product-photo" href="{{ route('shop.show',$item->slug) }}"><img src="{{ $item->image_url }}" alt="{{ $item->translated('name') }}"></a><div class="product-meta"><a class="product-name" href="{{ route('shop.show',$item->slug) }}">{{ $item->translated('name') }}</a><p class="price">{{ number_format($item->price,0,',',' ') }} <small>{{ __('so‘m') }}</small></p></div></article>@endforeach</div></section>
@endsection
