<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Filters\OrderByFilter;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $productQuery = $this->findFilter(Product::query(), $request);

        if ($search !== '') {
            $productQuery->where('title', 'like', '%'.$search.'%');
        }

        $categories = Category::all();
        $products = $productQuery->get();

        return view('frontend.products.all', compact('categories', 'products', 'search'));
    }

    private function findFilter(Builder $query, Request $request): Builder
    {
        $filters = $request->validate([
            'type' => ['nullable', 'in:free,paid'],
            'price' => ['nullable', 'in:10-100,101-200,201-300'],
            'sort' => ['nullable', 'in:default,popular,latest,oldest,price_asc,price_desc'],
        ]);

        if (($filters['type'] ?? null) === 'free') {
            $query->where('price', 0);
        } elseif (($filters['type'] ?? null) === 'paid') {
            $query->where('price', '>', 0);
        }

        $priceRanges = [
            '10-100' => [10000, 100000],
            '101-200' => [101000, 200000],
            '201-300' => [201000, 300000],
        ];

        if (! empty($filters['price'])) {
            $query->whereBetween('price', $priceRanges[$filters['price']]);
        }

        return OrderByFilter::apply($query, $filters['sort'] ?? 'default');
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
