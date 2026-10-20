<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class BascketController extends Controller
{
    private const COOKIE_MINUTES = 600;

    public function addToBascket(Request $request, $product_id): RedirectResponse
    {
        $product = Product::findOrFail($product_id);

        $basket = json_decode($request->cookie('basket') ?? '[]', true);
        $basket = is_array($basket) ? $basket : [];

        $basket[$product->id] = [
            'title' => $product->title,
            'price' => $product->price,
            'demo_url' => $product->demo_url,
        ];

        Cookie::queue(
            'basket',
            json_encode($basket, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            self::COOKIE_MINUTES
        );

        return to_route('frontend.products.show', $product->id)
            ->with('success', 'محصول به سبد خرید اضافه شد.');
    }
}
