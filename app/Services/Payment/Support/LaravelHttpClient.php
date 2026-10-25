<?php

declare(strict_types=1);

namespace App\Services\Payment\Support;

use App\Services\Payment\Contracts\HttpClientInterface;
use App\Services\Payment\Exceptions\GatewayException;
use Illuminate\Support\Facades\Http;
use Throwable;

final class LaravelHttpClient implements HttpClientInterface
{
    public function post(
        string $url,
        array $payload
    ): array {
        try {
            $response = Http::acceptJson()
                ->asJson()
                ->timeout(12)
                ->post($url, $payload);
        } catch (Throwable $exception) {
            throw new GatewayException(
                'Payment gateway is unreachable.',
                previous: $exception
            );
        }

        $body = $response->json();

        if (!is_array($body)) {
            throw new GatewayException(
                'Payment gateway returned invalid JSON.'
            );
        }

        if ($response->status() >= 500) {
            throw new GatewayException(
                'Payment gateway temporarily unavailable.'
            );
        }

        return $body;
    }
}
