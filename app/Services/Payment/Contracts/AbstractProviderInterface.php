<?php

declare(strict_types=1);

namespace App\Services\Payment\Contracts;

interface AbstractProviderInterface extends
    PayableInterface,
    VerifaibleInterface
{
    public function callbackTransactionId(
        array $callback
    ): ?string;

    public function callbackIndicatesSuccess(
        array $callback
    ): bool;
}
