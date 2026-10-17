<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrdersController extends Controller
{
    function all()
    {
        $orders = Order::Paginate(10);
        return view('admin.orders.all', compact('orders'));
    }
}
