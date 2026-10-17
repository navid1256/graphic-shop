<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class OrdersController extends Controller
{
    function all(Request $request)
    {
        $orders = Order::Paginate(10);
        return view('admin.orders.all', compact('orders'));
    }
}
