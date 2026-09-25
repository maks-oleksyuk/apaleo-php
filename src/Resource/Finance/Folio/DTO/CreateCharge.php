<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FinanceServiceType;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Shared\Enum\VatType;

final readonly class CreateCharge
{
    /** @param ?\DateTimeImmutable $businessDate defaults to the property's current business day */
    public function __construct(
        public string $name,
        public MonetaryValue $amount,
        public FinanceServiceType $serviceType,
        public VatType $vatType,
        public ?string $subAccountId = null,
        public ?string $receipt = null,
        public ?int $quantity = null,
        public ?\DateTimeImmutable $businessDate = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'amount' => $this->amount->toArray(),
            'serviceType' => $this->serviceType->value,
            'vatType' => $this->vatType->value,
            'subAccountId' => $this->subAccountId,
            'receipt' => $this->receipt,
            'quantity' => $this->quantity,
            'businessDate' => $this->businessDate?->format('Y-m-d'),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
