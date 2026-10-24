<?php

declare(strict_types=1);

namespace App\Services\Payment\Contracts;

use App\Services\Payment\Results\PaymentInitiation;

interface PayableInterface
{
    public function pay(
        RequestInterface $request
    ): PaymentInitiation;
}
