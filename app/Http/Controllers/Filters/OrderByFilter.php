<?php

namespace App\Http\Controllers\Filters;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;

class OrderByFilter
{
    public static function apply(Builder $query, string $orderBy): Builder
    {
        return match ($orderBy) {
            'popular' => self::mostPopular($query),
            'default' => self::default($query),
            'latest' => self::latest($query),
            'oldest' => self::oldest($query),
            'price_asc' => self::priceAscending($query),
            'price_desc' => self::priceDescending($query),
            default => self::default($query),
        };
    }

    private static function latest(Builder $query): Builder
    {
        return $query->latest();
    }

    private static function oldest(Builder $query): Builder
    {
        return $query->oldest();
    }

    private static function mostPopular(Builder $query): Builder
    {
        $paidSalesCount = OrderItem::query()
            ->selectRaw('COUNT(*)')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereColumn('order_items.product_id', 'products.id')
            ->where('orders.status', 'paied');

        return $query->orderByDesc($paidSalesCount);
    }

    private static function default(Builder $query): Builder
    {
        return $query;
    }

    private static function priceAscending(Builder $query): Builder
    {
        return $query->orderBy('price', 'asc');
    }

    private static function priceDescending(Builder $query): Builder
    {
        return $query->orderBy('price', 'desc');
    }
}
