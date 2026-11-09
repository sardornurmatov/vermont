<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $products = $this->products($request);
        $categories = Category::orderBy('name')->get();
        $catalogTitle = __('Barcha mahsulotlar');
        $catalogEyebrow = __('KOLLEKSIYA');
        $catalogType = 'all';

        return view('shop.index', compact('products', 'categories', 'catalogTitle', 'catalogEyebrow', 'catalogType'));
    }

    public function women(Request $request)
    {
        return $this->catalogPage($request, 'women');
    }

    public function men(Request $request)
    {
        return $this->catalogPage($request, 'men');
    }

    public function accessories(Request $request)
    {
        return $this->catalogPage($request, 'accessories');
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('active', true)
            ->with('categoryRecord')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->with(['reviews.user'])
            ->firstOrFail();

        $related = Product::where('active', true)
            ->with('categoryRecord')
            ->where('gender', $product->gender)
            ->where('id', '<>', $product->id)
            ->latest()
            ->limit(4)
            ->get();

        return view('shop.show', compact('product', 'related'));
    }

    private function catalogPage(Request $request, string $type)
    {
        $query = $this->baseQuery($request);

        if ($type === 'women') {
            $query->where('gender', 'women');
            $catalogTitle = __('Ayollar uchun');
            $catalogEyebrow = __('AYOLLAR KOLLEKSIYASI');
        } elseif ($type === 'men') {
            $query->where('gender', 'men');
            $catalogTitle = __('Erkaklar uchun');
            $catalogEyebrow = __('ERKAKLAR KOLLEKSIYASI');
        } else {
            $query->whereRaw('LOWER(category) IN (?, ?, ?, ?)', ['aksessuar', 'aksessuarlar', 'accessories', 'аксессуары']);
            $catalogTitle = __('Aksessuarlar');
            $catalogEyebrow = __('AKSESSUARLAR KOLLEKSIYASI');
        }

        $this->applyFiltersAndSort($query, $request, $type === 'women' ? 'women' : ($type === 'men' ? 'men' : null));
        $products = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $catalogType = $type;

        return view('shop.catalog', compact('products', 'categories', 'catalogTitle', 'catalogEyebrow', 'catalogType'));
    }

    private function products(Request $request): LengthAwarePaginator
    {
        $query = $this->baseQuery($request);
        $this->applyFiltersAndSort($query, $request);
        return $query->paginate(12)->withQueryString();
    }

    private function baseQuery(Request $request)
    {
        return Product::query()
            ->where('active', true)
            ->with('categoryRecord')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim((string) $request->query('q'));
                $query->where(function ($search) use ($term) {
                    $search->where('name', 'like', '%'.$term.'%')
                        ->orWhere('description', 'like', '%'.$term.'%')
                        ->orWhere('category', 'like', '%'.$term.'%');
                    foreach (['ru', 'en'] as $locale) {
                        $search->orWhere('translations->'.$locale.'->name', 'like', '%'.$term.'%')
                            ->orWhere('translations->'.$locale.'->description', 'like', '%'.$term.'%')
                            ->orWhereHas('categoryRecord', fn ($category) => $category->where('translations->'.$locale, 'like', '%'.$term.'%'));
                    }
                });
            });
    }

    private function applyFiltersAndSort($query, Request $request, ?string $lockedGender = null): void
    {
        if ($lockedGender === null && in_array($request->query('gender'), ['women', 'men'], true)) {
            $query->where('gender', $request->query('gender'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('min_price') && is_numeric($request->query('min_price'))) {
            $query->where('price', '>=', (int) $request->query('min_price'));
        }

        if ($request->filled('max_price') && is_numeric($request->query('max_price'))) {
            $query->where('price', '<=', (int) $request->query('max_price'));
        }

        match ($request->query('sort', 'newest')) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name_asc' => $query->orderBy('name'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };
    }
}
