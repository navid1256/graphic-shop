<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;

class PaymentsController extends Controller
{
    public function all()
    {
        $payment = Payment::paginate(10);
        return view('admin.payments.all', compact('payment'));
    }
}
