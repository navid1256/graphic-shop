<?php

declare(strict_types=1);

namespace App\Services\Payment\Results;

final class PaymentInitiation
{
    public function __construct(
        public readonly string $provider,
        public readonly string $transactionId,
        public readonly string $redirectUrl,
        public readonly array $rawResponse = []
    ) {}
}
