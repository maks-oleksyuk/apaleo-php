<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FinanceServiceType;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;

final readonly class CreateTransitoryCharge
{
    public function __construct(
        public string $name,
        public MonetaryValue $amount,
        public ?FinanceServiceType $serviceType = null,
        public ?string $receipt = null,
        public ?string $groupId = null,
        public ?int $quantity = null,
        public ?\DateTimeImmutable $businessDate = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'amount' => $this->amount->toArray(),
            'serviceType' => $this->serviceType?->value,
            'receipt' => $this->receipt,
            'groupId' => $this->groupId,
            'quantity' => $this->quantity,
            'businessDate' => $this->businessDate?->format('Y-m-d'),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
