<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentsController extends Controller
{
    public function all()
    {
        $payments = Payment::with('order.user')->paginate(10);
        return view('admin.payments.all', compact('payments'));
    }
}
