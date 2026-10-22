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

final class ZibalProvider implements AbstractProviderInterface
{
    private readonly HttpClientInterface $http;

    public function __construct(
        private readonly string $merchant,
        ?HttpClientInterface $http = null
    ) {
        if (trim($merchant) === '') {
            throw new InvalidArgumentException(
                'Zibal merchant is required.'
            );
        }

        $this->http = $http ?? new LaravelHttpClient();
    }

    public function pay(
        RequestInterface $request
    ): PaymentInitiation {
        $payload = [
            'merchant' => $this->merchant,
            'amount' => $request->amountRials(),
            'callbackUrl' => $request->callbackUrl(),
            'description' => $request->description(),
            'orderId' => $request->orderId(),
        ];

        if ($request->mobile() !== null) {
            $payload['mobile'] = $request->mobile();
        }

        $response = $this->http->post(
            'https://gateway.zibal.ir/v1/request',
            $payload
        );

        $trackId = $response['trackId'] ?? null;

        if (
            (int) ($response['result'] ?? 0) !== 100 ||
            !is_scalar($trackId) ||
            !preg_match('/^[0-9]+$/', (string) $trackId)
        ) {
            throw new GatewayException(
                'Zibal did not create a payment request.',
                isset($response['result'])
                    ? (int) $response['result']
                    : null
            );
        }

        $trackId = (string) $trackId;

        return new PaymentInitiation(
            'zibal',
            $trackId,
            'https://gateway.zibal.ir/start/' . $trackId,
            $response
        );
    }

    public function verify(
        string $transactionId,
        int $expectedAmountRials
    ): PaymentVerification {
        if (
            !preg_match('/^[0-9]+$/', $transactionId) ||
            $expectedAmountRials <= 0
        ) {
            throw new InvalidArgumentException(
                'Invalid Zibal transaction or amount.'
            );
        }

        $response = $this->http->post(
            'https://gateway.zibal.ir/v1/verify',
            [
                'merchant' => $this->merchant,
                'trackId' => $transactionId,
            ]
        );

        $code = isset($response['result'])
            ? (int) $response['result']
            : null;

        $status = match ($code) {
            100 => PaymentVerification::VERIFIED,
            201 => PaymentVerification::ALREADY_VERIFIED,
            default => PaymentVerification::FAILED,
        };

        $paidAmount = isset($response['amount']) &&
            filter_var(
                $response['amount'],
                FILTER_VALIDATE_INT
            ) !== false
            ? (int) $response['amount']
            : null;

        if (
            $status === PaymentVerification::VERIFIED &&
            $paidAmount !== $expectedAmountRials
        ) {
            throw new GatewayException(
                'Zibal verified amount does not match this order (or is missing).',
                $code
            );
        }

        if (
            $paidAmount !== null &&
            $paidAmount !== $expectedAmountRials
        ) {
            throw new GatewayException(
                'Zibal transaction amount mismatch.',
                $code
            );
        }

        $referenceId = isset($response['refNumber'])
            ? (string) $response['refNumber']
            : null;

        if (
            $status === PaymentVerification::VERIFIED &&
            ($referenceId === null || $referenceId === '')
        ) {
            throw new GatewayException(
                'Zibal confirmed payment without a reference number.',
                $code
            );
        }

        return new PaymentVerification(
            'zibal',
            $status,
            $transactionId,
            $referenceId,
            $paidAmount,
            $code,
            $response
        );
    }

    public function callbackTransactionId(
        array $callback
    ): ?string {
        $id = $callback['trackId'] ?? null;

        return is_scalar($id) &&
            preg_match('/^[0-9]+$/', (string) $id)
            ? (string) $id
            : null;
    }

    public function callbackIndicatesSuccess(
        array $callback
    ): bool {
        return (string) ($callback['success'] ?? '') === '1';
    }
}
