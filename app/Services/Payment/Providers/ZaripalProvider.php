<?php

declare(strict_types=1);

namespace App\Services\Payment\Providers;

use App\Services\Payment\Contracts\AbstractProviderInterface;
use App\Services\Payment\Contracts\HttpClientInterface;
use App\Services\Payment\Contracts\RequestInterface;
use App\Services\Payment\Exceptions\GatewayException;
use App\Services\Payment\Results\PaymentInitiation;
use App\Services\Payment\Results\PaymentVerification;
use App\Services\Payment\Support\LaravelHttpClient;
use InvalidArgumentException;

final class ZarinpalProvider implements AbstractProviderInterface
{
    private readonly HttpClientInterface $http;

    public function __construct(
        private readonly string $merchantId,
        private readonly bool $sandbox = true,
        ?HttpClientInterface $http = null
    ) {
        if (!preg_match(
            '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i',
            $merchantId
        )) {
            throw new InvalidArgumentException(
                'Zarinpal Merchant ID must be a UUID.'
            );
        }

        $this->http = $http ?? new LaravelHttpClient();
    }

    private function baseUrl(): string
    {
        return $this->sandbox
            ? 'https://sandbox.zarinpal.com'
            : 'https://payment.zarinpal.com';
    }

    public function pay(
        RequestInterface $request
    ): PaymentInitiation {
        $metadata = [
            'order_id' => $request->orderId(),
        ];

        if ($request->mobile() !== null) {
            $metadata['mobile'] = $request->mobile();
        }

        if ($request->email() !== null) {
            $metadata['email'] = $request->email();
        }

        $response = $this->http->post(
            $this->baseUrl() . '/pg/v4/payment/request.json',
            [
                'merchant_id' => $this->merchantId,
                'amount' => $request->amountRials(),
                'currency' => 'IRR',
                'callback_url' => $request->callbackUrl(),
                'description' => $request->description(),
                'metadata' => $metadata,
            ]
        );

        $data = $response['data'] ?? [];
        $authority = $data['authority'] ?? null;

        if (
            (int) ($data['code'] ?? 0) !== 100 ||
            !is_string($authority) ||
            !preg_match('/^[A-Za-z0-9_-]{10,128}$/', $authority)
        ) {
            $code = isset($data['code'])
                ? (int) $data['code']
                : (
                    isset($response['errors']['code'])
                    ? (int) $response['errors']['code']
                    : null
                );

            throw new GatewayException(
                'Zarinpal did not create a payment request.',
                $code
            );
        }

        return new PaymentInitiation(
            'zarinpal',
            $authority,
            $this->baseUrl() . '/pg/StartPay/' . $authority,
            $response
        );
    }

    public function verify(
        string $transactionId,
        int $expectedAmountRials
    ): PaymentVerification {
        if (
            !preg_match(
                '/^[A-Za-z0-9_-]{10,128}$/',
                $transactionId
            ) ||
            $expectedAmountRials <= 0
        ) {
            throw new InvalidArgumentException(
                'Invalid Zarinpal transaction or amount.'
            );
        }

        $response = $this->http->post(
            $this->baseUrl() . '/pg/v4/payment/verify.json',
            [
                'merchant_id' => $this->merchantId,
                'amount' => $expectedAmountRials,
                'authority' => $transactionId,
            ]
        );

        $data = $response['data'] ?? [];

        $code = isset($data['code'])
            ? (int) $data['code']
            : (
                isset($response['errors']['code'])
                ? (int) $response['errors']['code']
                : null
            );

        $status = match ($code) {
            100 => PaymentVerification::VERIFIED,
            101 => PaymentVerification::ALREADY_VERIFIED,
            default => PaymentVerification::FAILED,
        };

        $referenceId = isset($data['ref_id'])
            ? (string) $data['ref_id']
            : null;

        if (
            $status === PaymentVerification::VERIFIED &&
            ($referenceId === null || $referenceId === '')
        ) {
            throw new GatewayException(
                'Zarinpal confirmed payment without a reference ID.',
                $code
            );
        }

        return new PaymentVerification(
            'zarinpal',
            $status,
            $transactionId,
            $referenceId,
            $status === PaymentVerification::VERIFIED
                ? $expectedAmountRials
                : null,
            $code,
            $response
        );
    }

    public function callbackTransactionId(
        array $callback
    ): ?string {
        $token = $callback['Authority'] ?? null;

        return is_string($token) &&
            preg_match('/^[A-Za-z0-9_-]{10,128}$/', $token)
            ? $token
            : null;
    }

    public function callbackIndicatesSuccess(
        array $callback
    ): bool {
        return ($callback['Status'] ?? null) === 'OK';
    }
}
