<?php

namespace App\Services;

use App\Services\Payment\Contracts\PayableInterface;
use App\Services\Payment\Contracts\RequestInterface;
use InvalidArgumentException;

class PaymentService
{
    private const IDPAY = 'IDPayProvider';
    private const ZARINPAL = 'ZarinpalProvider';

    public function __construct(private string $providerName, private RequestInterface $request) {}

    public function pay()
    {
        return $this->findProvider()->pay();
    }

    private function findProvider(): PayableInterface
    {
        $providerClass = 'App\\Services\\Payment\\Providers\\' . $this->providerName;

        if (! class_exists($providerClass)) {
            throw new InvalidArgumentException("Payment provider [{$this->providerName}] does not exist.");
        }

        return new $providerClass();
    }
}
