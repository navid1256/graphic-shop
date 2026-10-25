<?php

declare(strict_types=1);

namespace App\Services\Payment\Contracts;

interface HttpClientInterface
{
    public function post(
        string $url,
        array $payload
    ): array;
}
