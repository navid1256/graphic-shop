<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function pay()
    {
        $iDPayRequest = new IDPayRequest([
            
        ]);

        $paymentService = new PaymentService(PaymentService::IDPAY, $iDPayRequest);
        $paymentService->pay();
    }
}
