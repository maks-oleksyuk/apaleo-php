<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Refund\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;

/** A refund paid out outside apaleo and just recorded on the folio. */
final readonly class CreateFolioRefund
{
    public function __construct(
        public PaymentMethod $method,
        public MonetaryValue $amount,
        public ?string $receipt = null,
        public ?string $reason = null,
        public ?\DateTimeImmutable $businessDate = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'method' => $this->method->value,
            'amount' => $this->amount->toArray(),
            'receipt' => $this->receipt,
            'reason' => $this->reason,
            'businessDate' => $this->businessDate?->format('Y-m-d'),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
