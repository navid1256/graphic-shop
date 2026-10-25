<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\ProductObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        Paginator::useBootstrapFive();

        View::composer('layouts.frontend.header', function ($view) {
            $basket = json_decode(request()->cookie('basket') ?? '[]', true);
            $productIds = is_array($basket) ? array_keys($basket) : [];
            $basketProducts = empty($productIds)
                ? collect()
                : Product::whereIn('id', $productIds)->get(['id', 'title', 'price', 'thumbnail_url']);

            $view->with([
                'basketProducts' => $basketProducts,
                'basketCount' => $basketProducts->count(),
                'basketTotal' => $basketProducts->sum('price'),
            ]);
        });
    }
}
