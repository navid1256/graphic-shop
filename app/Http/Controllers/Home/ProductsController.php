<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductsController extends Controller
{
    public function index()
    {
        $products = Product::latest()->get();

        return view('frontend.products.all', compact('products'));
    }

    public function show($product_id)
    {
        $product = Product::with(['category', 'owner'])->findOrFail($product_id);

        $similarProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()
            ->take(4)
            ->get();

        return view('frontend.products.show', compact('product', 'similarProducts'));
    }
}
