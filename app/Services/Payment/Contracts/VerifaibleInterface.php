<?php

declare(strict_types=1);

namespace App\Services\Payment\Contracts;

use App\Services\Payment\Results\PaymentVerification;

interface VerifaibleInterface
{
    public function verify(
        string $transactionId,
        int $expectedAmountRials
    ): PaymentVerification;
}
