<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        abort_unless($product->active, 404);

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:1500',
        ]);

        $product->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $data
        );

        return back()->with('status', __('Sharhingiz saqlandi.'));
    }
}
