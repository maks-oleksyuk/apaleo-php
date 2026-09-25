<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Payment\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Shared\DTO\PaidCharge;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;

/** A payment taken outside apaleo (cash, bank transfer, a card run on a non-integrated terminal) and just recorded. */
final readonly class CreateCustomPayment
{
    /** @param list<PaidCharge> $paidCharges */
    public function __construct(
        public PaymentMethod $method,
        public MonetaryValue $amount,
        public ?string $receipt = null,
        public ?\DateTimeImmutable $businessDate = null,
        public array $paidCharges = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'method' => $this->method->value,
            'amount' => $this->amount->toArray(),
            'receipt' => $this->receipt,
            'businessDate' => $this->businessDate?->format('Y-m-d'),
            'paidCharges' => array_map(static fn (PaidCharge $c): array => $c->toArray(), $this->paidCharges) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
