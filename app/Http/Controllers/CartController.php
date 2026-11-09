<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $lines = collect($cart)->map(function ($line, $id) use ($products) { $product = $products->get($id); return $product ? ['product'=>$product,'quantity'=>$line['quantity'],'variant'=>$line['variant'] ?? '','subtotal'=>$product->price * $line['quantity']] : null; })->filter();
        return view('cart.index', ['lines'=>$lines,'total'=>$lines->sum('subtotal')]);
    }
    public function add(Request $request)
    {
        $data = $request->validate(['product_id'=>'required|exists:products,id','quantity'=>'nullable|integer|min:1|max:10','variant'=>'nullable|string|max:60']);
        $product = Product::where('active',true)->findOrFail($data['product_id']);
        $cart = $request->session()->get('cart', []);
        $newQuantity = ($cart[$product->id]['quantity'] ?? 0) + ($data['quantity'] ?? 1);
        if ($product->stock < $newQuantity) return back()->withErrors(['cart'=>__('Bu mahsulotdan yetarli qolmagan.')]);
        $cart[$product->id] = ['quantity'=>min(10,$newQuantity),'variant'=>$data['variant'] ?? ''];
        $request->session()->put('cart',$cart);
        return back()->with('status',__('Mahsulot savatga qo‘shildi.'));
    }
    public function update(Request $request)
    {
        $data = $request->validate(['items'=>'required|array','items.*.quantity'=>'required|integer|min:0|max:10']);
        $cart = $request->session()->get('cart', []);
        foreach ($data['items'] as $id=>$line) { if ((int)($line['quantity'] ?? 0) === 0) unset($cart[$id]); elseif (isset($cart[$id])) $cart[$id]['quantity'] = (int)$line['quantity']; }
        $request->session()->put('cart',$cart);
        return redirect()->route('cart.index')->with('status',__('Savat yangilandi.'));
    }
}
