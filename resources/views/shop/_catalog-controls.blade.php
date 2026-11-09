<form class="catalog-controls" method="get" action="{{ url()->current() }}">
    @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
    @if($catalogType === 'all')
        <div class="filter-pills"><a class="active" href="{{ route('shop.index') }}#products">{{ __('Barchasi') }}</a><a href="{{ route('shop.women') }}">{{ __('Ayollar') }}</a><a href="{{ route('shop.men') }}">{{ __('Erkaklar') }}</a><a href="{{ route('shop.accessories') }}">{{ __('Aksessuarlar') }}</a></div>
    @endif
    <div class="catalog-filter-row">
        <label>{{ __('Kategoriya') }}<select name="category" onchange="this.form.submit()"><option value="">{{ __('Barcha kategoriyalar') }}</option>@foreach($categories as $category)<option value="{{ $category->name }}" @selected(request('category')===$category->name)>{{ $category->translatedName() }}</option>@endforeach</select></label>
        <label>{{ __('Eng past narx') }}<input name="min_price" type="number" min="0" value="{{ request('min_price') }}" placeholder="0"></label>
        <label>{{ __('Eng yuqori narx') }}<input name="max_price" type="number" min="0" value="{{ request('max_price') }}" placeholder="{{ __('Istalgan') }}"></label>
        <label>{{ __('Saralash') }}<select name="sort" onchange="this.form.submit()"><option value="newest" @selected(request('sort','newest')==='newest')>{{ __('Eng yangilari') }}</option><option value="oldest" @selected(request('sort')==='oldest')>{{ __('Avvalgilari') }}</option><option value="price_asc" @selected(request('sort')==='price_asc')>{{ __('Narx: arzondan qimmatga') }}</option><option value="price_desc" @selected(request('sort')==='price_desc')>{{ __('Narx: qimmatdan arzonga') }}</option><option value="name_asc" @selected(request('sort')==='name_asc')>{{ __('Nomi bo‘yicha') }}</option></select></label>
        <button class="button button-outline" type="submit">{{ __('Filtrlash') }}</button>
        @if(request()->query())<a class="quiet-link" href="{{ url()->current() }}">{{ __('Tozalash') }}</a>@endif
    </div>
</form>
