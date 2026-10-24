<?php

declare(strict_types=1);

namespace App\Services\Payment\Requests;

use App\Services\Payment\Contracts\RequestInterface;
use InvalidArgumentException;

class PaymentRequest implements RequestInterface
{
    public function __construct(
        private readonly string $orderId,
        private readonly int $amountRials,
        private readonly string $callbackUrl,
        private readonly string $description,
        private readonly ?string $mobile = null,
        private readonly ?string $email = null
    ) {
        if (
            trim($orderId) === '' ||
            strlen($orderId) > 100
        ) {
            throw new InvalidArgumentException(
                'orderId must be nonempty (max 100 characters).'
            );
        }

        if ($amountRials <= 0) {
            throw new InvalidArgumentException(
                'Payment amount must be a positive integer number of rials.'
            );
        }

        if (
            !filter_var($callbackUrl, FILTER_VALIDATE_URL) ||
            !in_array(
                parse_url($callbackUrl, PHP_URL_SCHEME),
                ['http', 'https'],
                true
            )
        ) {
            throw new InvalidArgumentException(
                'callbackUrl must be an absolute HTTP(S) URL.'
            );
        }

        if (trim($description) === '') {
            throw new InvalidArgumentException(
                'Payment description cannot be empty.'
            );
        }

        if (
            $email !== null &&
            !filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            throw new InvalidArgumentException(
                'Invalid email address.'
            );
        }
    }

    public function orderId(): string
    {
        return $this->orderId;
    }

    public function amountRials(): int
    {
        return $this->amountRials;
    }

    public function callbackUrl(): string
    {
        return $this->callbackUrl;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function mobile(): ?string
    {
        return $this->mobile;
    }

    public function email(): ?string
    {
        return $this->email;
    }
}
