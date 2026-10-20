<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\Cookie;

class CheckoutController extends Controller
{
    public function show()
    {
        $basket = json_decode(Cookie::get('basket') ?? '[]', true);
        $productIds = is_array($basket) ? array_keys($basket) : [];
        $products = empty($productIds)
            ? collect()
            : Product::whereIn('id', $productIds)->get(['id', 'title', 'price', 'demo_url', 'thumbnail_url']);

        return view('frontend.checkout.checkout', compact('products'));
    }
}
