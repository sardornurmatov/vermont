<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\ProductTranslator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductAdminController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $cardNumber = StoreSetting::get('payment_card_number', config('services.payments.card_number'));
        $cardHolder = StoreSetting::get('payment_card_holder', config('services.payments.card_holder'));

        return view('admin.products', compact('categories', 'cardNumber', 'cardHolder'));
    }

    public function editList()
    {
        $products = Product::with('categoryRecord')->latest()->paginate(20);

        return view('admin.product-edit', compact('products'));
    }

    public function storeCategory(Request $request, ProductTranslator $translator)
    {
        $data = $request->validate(['name' => 'required|string|max:80']);
        $name = trim(preg_replace('/\s+/u', ' ', $data['name']));
        $category = Category::firstOrCreate(['name' => $name]);
        $this->translateCategory($category, $translator);
        $message = $category->wasRecentlyCreated
            ? __('Kategoriya saqlandi.')
            : __('Bu kategoriya avvaldan mavjud.');

        return back()->with('status', $message);
    }

    public function settings(Request $request)
    {
        $data = $request->validate([
            'card_number' => 'required|string|min:12|max:32',
            'card_holder' => 'required|string|max:100',
        ]);

        StoreSetting::put('payment_card_number', $data['card_number']);
        StoreSetting::put('payment_card_holder', $data['card_holder']);

        return back()->with('status', __('To‘lov karta ma’lumotlari yangilandi.'));
    }

    public function store(Request $request, ProductTranslator $translator)
    {
        $data = $this->validated($request);
        $data['category'] = $this->cleanCategory($data['category']);
        $this->translateCategory(Category::firstOrCreate(['name' => $data['category']]), $translator);
        $data['translations'] = $this->translations($data, [], $translator);
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(5));
        $data['colors'] = $this->list($data['colors'] ?? '');
        $data['sizes'] = $this->list($data['sizes'] ?? '');

        Product::create($data);

        return back()->with('status', __('Mahsulot qo‘shildi.'));
    }

    public function update(Request $request, Product $product, ProductTranslator $translator)
    {
        $data = $this->validated($request);
        $data['category'] = $this->cleanCategory($data['category']);
        $this->translateCategory(Category::firstOrCreate(['name' => $data['category']]), $translator);
        $existingTranslations = $product->translations ?? [];
        if ($data['name'] !== $product->name) {
            foreach (['ru', 'en'] as $locale) unset($existingTranslations[$locale]['name']);
        }
        if (($data['description'] ?? '') !== ($product->description ?? '')) {
            foreach (['ru', 'en'] as $locale) unset($existingTranslations[$locale]['description']);
        }
        $data['translations'] = $this->translations($data, $existingTranslations, $translator);
        $data['slug'] = Str::slug($data['name']).'-'.$product->id;
        $data['colors'] = $this->list($data['colors'] ?? '');
        $data['sizes'] = $this->list($data['sizes'] ?? '');

        $product->update($data);

        return back()->with('status', __('Mahsulot yangilandi.'));
    }

    public function destroy(Product $product)
    {
        $product->update(['active' => false]);
        return back()->with('status', __('Mahsulot vitrindan yashirildi.'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:180',
            'category' => 'required|string|max:80',
            'gender' => 'required|in:women,men',
            'description' => 'nullable|string|max:2000',
            'name_ru' => 'nullable|string|max:180',
            'name_en' => 'nullable|string|max:180',
            'description_ru' => 'nullable|string|max:2000',
            'description_en' => 'nullable|string|max:2000',
            'price' => 'required|integer|min:1',
            'old_price' => 'nullable|integer|min:1',
            'image_url' => 'nullable|url|max:1000',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'colors' => 'nullable|string|max:300',
            'sizes' => 'nullable|string|max:200',
            'stock' => 'required|integer|min:0',
            'active' => 'nullable|boolean',
            'featured' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image_url'] = url(Storage::url($path));
        }

        // Rasm fayli yuklangan bo‘lsa, URL talab qilinmaydi.
        if (!$request->hasFile('image') && empty($data['image_url'])) {
            $request->validate(['image_url' => 'required|url']);
        }

        unset($data['image']);

        return $data + [
            'active' => $request->boolean('active'),
            'featured' => $request->boolean('featured'),
        ];
    }

    private function translations(array &$data, array $existing, ProductTranslator $translator): array
    {
        $translations = $existing;

        foreach (['ru', 'en'] as $locale) {
            $nameKey = 'name_'.$locale;
            $descriptionKey = 'description_'.$locale;
            $name = trim((string) ($data[$nameKey] ?? ''));
            $description = trim((string) ($data[$descriptionKey] ?? ''));

            if ($name !== '') {
                data_set($translations, $locale.'.name', $name);
            }
            if ($description !== '') {
                data_set($translations, $locale.'.description', $description);
            }

            unset($data[$nameKey], $data[$descriptionKey]);
        }

        // If translation fields are left empty, fill them automatically when the API key is configured.
        if (filled(config('services.google_translate.key'))) {
            foreach (['ru', 'en'] as $locale) {
                foreach (['name', 'description'] as $field) {
                    if (filled(data_get($translations, $locale.'.'.$field))) {
                        continue;
                    }
                    $source = trim((string) ($data[$field] ?? ''));
                    if ($source === '') {
                        continue;
                    }
                    try {
                        data_set($translations, $locale.'.'.$field, $translator->translate($source, $locale));
                    } catch (\Throwable $exception) {
                        report($exception);
                        // Saving the product must still work if the translation provider is unavailable.
                    }
                }
            }
        }

        return $translations;
    }

    private function cleanCategory(string $name): string
    {
        return trim(preg_replace('/\s+/u', ' ', $name));
    }

    private function translateCategory(Category $category, ProductTranslator $translator): void
    {
        if (!filled(config('services.google_translate.key'))) {
            return;
        }

        $translations = $category->translations ?? [];
        foreach (['ru', 'en'] as $locale) {
            if (filled($translations[$locale] ?? null)) {
                continue;
            }
            try {
                $translations[$locale] = $translator->translate($category->name, $locale);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        if ($translations !== ($category->translations ?? [])) {
            $category->update(['translations' => $translations]);
        }
    }

    private function list(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
