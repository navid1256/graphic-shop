<?php

declare(strict_types=1);

namespace App\Services\Payment\Contracts;

interface RequestInterface
{
    public function orderId(): string;

    public function amountRials(): int;

    public function callbackUrl(): string;

    public function description(): string;

    public function mobile(): ?string;

    public function email(): ?string;
}
