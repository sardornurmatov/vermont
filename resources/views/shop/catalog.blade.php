@extends('layouts.app')
@section('title', $catalogTitle.' — Vermontshop')
@section('content')
<section class="catalog-hero"><p class="eyebrow">{{ $catalogEyebrow }}</p><h1>{{ $catalogTitle }}</h1><p>{{ __('O‘zingizga mos mahsulotni qidiruv va saralash orqali toping.') }}</p></section>
<section class="collection-section catalog-section" id="products">
    @include('shop._catalog-controls')
    @include('shop._product-grid')
</section>
@endsection
