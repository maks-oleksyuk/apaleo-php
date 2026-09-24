<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Refund\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;

/** Refunds (part of) a specific payment, online through the payment provider where possible. */
final readonly class CreatePaymentRefund
{
    public function __construct(
        public MonetaryValue $amount,
        public ?string $reason = null,
        public ?\DateTimeImmutable $businessDate = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount->toArray(),
            'reason' => $this->reason,
            'businessDate' => $this->businessDate?->format('Y-m-d'),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
