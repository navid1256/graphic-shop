<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $productQuery = Product::latest();

        if ($search !== '') {
            $productQuery->where('title', 'like', '%'.$search.'%');
        }

        $categories = Category::all();
        $products = $productQuery->get();

        return view('frontend.products.all', compact('categories', 'products', 'search'));
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
