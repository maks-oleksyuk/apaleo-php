<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Routing\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FinanceServiceType;

final readonly class CreateRoutingChargeFilter
{
    /**
     * @param list<string>             $folioIds
     * @param list<string>             $subAccountIds
     * @param list<FinanceServiceType> $serviceTypes
     * @param list<string>             $serviceIds
     */
    public function __construct(
        public array $folioIds = [],
        public array $subAccountIds = [],
        public array $serviceTypes = [],
        public array $serviceIds = [],
        public ?\DateTimeImmutable $from = null,
        public ?\DateTimeImmutable $to = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'folioIds' => $this->folioIds ?: null,
            'subAccountIds' => $this->subAccountIds ?: null,
            'serviceTypes' => array_map(static fn (FinanceServiceType $t): string => $t->value, $this->serviceTypes) ?: null,
            'serviceIds' => $this->serviceIds ?: null,
            'from' => $this->from?->format('Y-m-d'),
            'to' => $this->to?->format('Y-m-d'),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
