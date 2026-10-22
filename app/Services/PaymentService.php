<?php

declare(strict_types=1);

namespace App\Services;





use App\Services\Payment\Contracts\AbstractProviderInterface;
use App\Services\Payment\Contracts\RequestInterface;
use App\Services\Payment\Exceptions\InvalidPaymentCallbackException;
use App\Services\Payment\Exceptions\ProvideNotFoundException;
use App\Services\Payment\Providers\ZarinpalProvider;
use App\Services\Payment\Providers\ZibalProvider;
use App\Services\Payment\Results\PaymentInitiation;
use App\Services\Payment\Results\PaymentVerification;

final class PaymentService
{
    public const ZARINPAL = 'zarinpal';
    public const ZIBAL = 'zibal';

    private readonly AbstractProviderInterface $provider;

    public function __construct(
        private readonly string $providerName,
        private readonly RequestInterface $request,
        ?AbstractProviderInterface $provider = null
    ) {
        $this->provider = $provider
            ?? $this->findProvider($providerName);
    }

    public function pay(): PaymentInitiation
    {
        return $this->provider->pay($this->request);
    }

    public function verify(
        string $transactionId
    ): PaymentVerification {
        return $this->provider->verify(
            $transactionId,
            $this->request->amountRials()
        );
    }

    public function verifyCallback(
        array $callback,
        string $persistedTransactionId
    ): PaymentVerification {
        $receivedId = $this->provider
            ->callbackTransactionId($callback);

        if (
            $receivedId === null ||
            !hash_equals($persistedTransactionId, $receivedId)
        ) {
            throw new InvalidPaymentCallbackException(
                'Callback transaction ID does not match stored payment.'
            );
        }

        if (
            !$this->provider->callbackIndicatesSuccess($callback)
        ) {
            return new PaymentVerification(
                $this->providerName,
                PaymentVerification::FAILED,
                $receivedId
            );
        }

        return $this->verify($receivedId);
    }

    public function callbackTransactionId(
        array $callback
    ): ?string {
        return $this->provider
            ->callbackTransactionId($callback);
    }

    private function findProvider(
        string $name
    ): AbstractProviderInterface {
        return match ($name) {
            self::ZARINPAL => new ZarinpalProvider(
                (string) config(
                    'payments.providers.zarinpal.merchant_id',
                    ''
                ),
                (bool) config(
                    'payments.providers.zarinpal.sandbox',
                    true
                ),
                autoVerify: (bool) config('payments.providers.zarinpal.auto_verify', false),
            ),

            self::ZIBAL => new ZibalProvider(
                (string) config(
                    'payments.providers.zibal.merchant',
                    ''
                ),
            ),

            default => throw new ProvideNotFoundException(
                "Unknown payment provider [{$name}]."
            ),
        };
    }
}
