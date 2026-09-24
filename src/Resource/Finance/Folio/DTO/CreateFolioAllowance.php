<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FinanceServiceType;

/** An allowance on the folio as a whole, not tied to one charge. */
final readonly class CreateFolioAllowance
{
    public function __construct(
        public string $reason,
        public MonetaryValue $amount,
        public FinanceServiceType $serviceType,
        public VatType $vatType,
        public ?string $subAccountId = null,
        public ?\DateTimeImmutable $businessDate = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'reason' => $this->reason,
            'amount' => $this->amount->toArray(),
            'serviceType' => $this->serviceType->value,
            'vatType' => $this->vatType->value,
            'subAccountId' => $this->subAccountId,
            'businessDate' => $this->businessDate?->format('Y-m-d'),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
