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
        ?HttpClientInterface $http = null,
        private readonly bool $autoVerify = false,
    ) {
        if (!preg_match(
            '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i',
            $merchantId
        )) {
            throw new InvalidArgumentException(
                'Zarinpal merchant_id must be a UUID.'
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
        if ($request->amountRials() <= 0) {
            throw new InvalidArgumentException(
                'Payment amount must be positive.'
            );
        }

        $description = trim($request->description());
        $length = preg_match_all('/./us', $description);

        if ($length === false || $length < 1 || $length > 500) {
            throw new InvalidArgumentException(
                'Description must contain 1 to 500 characters.'
            );
        }

        $metadata = [
            'order_id' => $request->orderId(),
            'auto_verify' => $this->autoVerify,
        ];

        if ($request->mobile()) {
            $metadata['mobile'] = $request->mobile();
        }

        if ($request->email()) {
            $metadata['email'] = $request->email();
        }

        $response = $this->http->post(
            $this->baseUrl() . '/pg/v4/payment/request.json',
            [
                'merchant_id' => $this->merchantId,
                'amount' => $request->amountRials(),
                'currency' => 'IRR',
                'description' => $description,
                'callback_url' => $request->callbackUrl(),
                'metadata' => $metadata,
            ]
        );

        $data = $response['data'] ?? null;
        $code = is_array($data) ? ($data['code'] ?? null) : null;
        $authority = is_array($data)
            ? ($data['authority'] ?? null)
            : null;

        if (
            !is_numeric($code) ||
            (int) $code !== 100 ||
            !is_string($authority) ||
            !preg_match('/^[A-Za-z0-9_-]{10,128}$/', $authority)
        ) {
            throw new GatewayException(
                'Zarinpal payment request failed.',
                self::gatewayCode($response)
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
        if (!preg_match(
            '/^[A-Za-z0-9_-]{10,128}$/',
            $transactionId
        )) {
            throw new InvalidArgumentException(
                'Invalid Zarinpal Authority.'
            );
        }

        if ($expectedAmountRials <= 0) {
            throw new InvalidArgumentException(
                'Invalid payment amount.'
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

        $code = self::gatewayCode($response);

        $status = match ($code) {
            100 => PaymentVerification::VERIFIED,
            101 => PaymentVerification::ALREADY_VERIFIED,
            default => PaymentVerification::FAILED,
        };

        $data = is_array($response['data'] ?? null)
            ? $response['data']
            : [];

        $referenceId = isset($data['ref_id']) &&
            is_scalar($data['ref_id'])
            ? (string) $data['ref_id']
            : null;

        if (
            $status === PaymentVerification::VERIFIED &&
            ($referenceId === null || $referenceId === '')
        ) {
            throw new GatewayException(
                'Verified payment has no ref_id.',
                $code
            );
        }

        return new PaymentVerification(
            'zarinpal',
            $status,
            $transactionId,
            $referenceId,
            null,
            $code,
            $response
        );
    }

    public function callbackTransactionId(
        array $callback
    ): ?string {
        $authority = $callback['Authority'] ?? null;

        return is_string($authority) &&
            preg_match('/^[A-Za-z0-9_-]{10,128}$/', $authority)
            ? $authority
            : null;
    }

    public function callbackIndicatesSuccess(
        array $callback
    ): bool {
        return ($callback['Status'] ?? null) === 'OK';
    }

    private static function gatewayCode(
        array $response
    ): ?int {
        foreach (
            [
                $response['data'] ?? null,
                $response['errors'] ?? null,
            ] as $section
        ) {
            if (
                is_array($section) &&
                isset($section['code']) &&
                is_numeric($section['code'])
            ) {
                return (int) $section['code'];
            }
        }

        return null;
    }
}
