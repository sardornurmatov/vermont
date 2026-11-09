{{-- Backward-compatible form partial for @include('admin_product-form-fields'). --}}
@php($editingProduct = $product ?? null)

<input name="name" value="{{ old('name', $editingProduct?->name) }}" placeholder="{{ __('Mahsulot nomi (UZ)') }}" required>
<input name="name_ru" value="{{ old('name_ru', data_get($editingProduct?->translations, 'ru.name')) }}" placeholder="{{ __('Mahsulot nomi (RU)') }}">
<input name="name_en" value="{{ old('name_en', data_get($editingProduct?->translations, 'en.name')) }}" placeholder="{{ __('Mahsulot nomi (EN)') }}">
<input name="category" value="{{ old('category', $editingProduct?->category) }}" placeholder="{{ __('Kategoriya') }}" required>
<select name="gender" required>
    <option value="women" @selected(old('gender', $editingProduct?->gender ?? 'women') === 'women')>{{ __('Ayollar') }}</option>
    <option value="men" @selected(old('gender', $editingProduct?->gender) === 'men')>{{ __('Erkaklar') }}</option>
</select>
<input name="price" type="number" min="1" value="{{ old('price', $editingProduct?->price) }}" placeholder="{{ __('Narxi (so‘m)') }}" required>
<input name="old_price" type="number" min="1" value="{{ old('old_price', $editingProduct?->old_price) }}" placeholder="{{ __('Eski narx (ixtiyoriy)') }}">
<input name="stock" type="number" min="0" value="{{ old('stock', $editingProduct?->stock ?? 0) }}" placeholder="{{ __('Ombordagi soni') }}" required>
<input name="image_url" type="url" value="{{ old('image_url', $editingProduct?->image_url) }}" placeholder="{{ __('Rasm havolasi (ixtiyoriy)') }}">
<input name="image" type="file" accept="image/jpeg,image/png,image/webp">
<input name="colors" value="{{ old('colors', implode(', ', $editingProduct?->colors ?? [])) }}" placeholder="{{ __('Ranglar: Qora, Oq') }}">
<input name="sizes" value="{{ old('sizes', implode(', ', $editingProduct?->sizes ?? [])) }}" placeholder="{{ __('O‘lchamlar: S, M, L') }}">
<textarea name="description" placeholder="{{ __('Tavsif (UZ)') }}">{{ old('description', $editingProduct?->description) }}</textarea>
<textarea name="description_ru" placeholder="{{ __('Tavsif (RU)') }}">{{ old('description_ru', data_get($editingProduct?->translations, 'ru.description')) }}</textarea>
<textarea name="description_en" placeholder="{{ __('Tavsif (EN)') }}">{{ old('description_en', data_get($editingProduct?->translations, 'en.description')) }}</textarea>
<label><input type="checkbox" name="active" value="1" @checked(old('active', $editingProduct?->active ?? true))> {{ __('Do‘konda ko‘rsatish') }}</label>
<label><input type="checkbox" name="featured" value="1" @checked(old('featured', $editingProduct?->featured ?? false))> {{ __('Tavsiya etilgan') }}</label>
