<?php

declare(strict_types=1);

namespace App\Services\Payment\Exceptions;

class GatewayException extends PaymentException
{
    public function __construct(
        string $message,
        public readonly ?int $gatewayCode = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            $gatewayCode ?? 0,
            $previous
        );
    }
}
