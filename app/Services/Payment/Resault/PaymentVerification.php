<?php

declare(strict_types=1);

namespace App\Services\Payment\Results;

final class PaymentVerification
{
    public const VERIFIED = 'verified';
    public const ALREADY_VERIFIED = 'already_verified';
    public const FAILED = 'failed';

    public function __construct(
        public readonly string $provider,
        public readonly string $status,
        public readonly string $transactionId,
        public readonly ?string $referenceId = null,
        public readonly ?int $paidAmountRials = null,
        public readonly ?int $gatewayCode = null,
        public readonly array $rawResponse = []
    ) {}

    public function isNewlyVerified(): bool
    {
        return $this->status === self::VERIFIED;
    }
}
