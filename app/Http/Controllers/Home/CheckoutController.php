<?php

namespace App\Http\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CheckoutController extends Controller
{
    public function prepareSelection(Request $request)
    {
        $customer = $request->validate([
            'family' => ['required', 'string', 'max:120'],
            'mobile' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
            'email' => ['nullable', 'email', 'max:254'],
        ]);

        $request->session()->put('checkout.customer', $customer);

        return redirect()->route('frontend.checkout.select');
    }

    public function select(Request $request)
    {
        $customer = $request->session()->get('checkout.customer');

        if (! $customer) {
            return redirect()->route('frontend.checkout')
                ->withErrors(['payment' => 'ابتدا اطلاعات کاربری خود را وارد کنید.']);
        }

        $basket = json_decode($request->cookie('basket', '[]'), true);
        $productIds = is_array($basket) ? array_keys($basket) : [];
        $products = Product::whereIn('id', $productIds)->get(['id', 'price']);
        $total = $products->sum('price');

        if ($products->isEmpty() || $total <= 0 || $total > intdiv(PHP_INT_MAX, 10)) {
            return redirect()->route('frontend.checkout')
                ->withErrors(['payment' => 'سبد خرید خالی است یا مبلغ آن معتبر نیست.']);
        }

        return view('frontend.checkout.select', compact('customer', 'total'));
    }

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
