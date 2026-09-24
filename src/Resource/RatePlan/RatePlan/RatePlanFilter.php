<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan;

use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ChannelCode;

final readonly class RatePlanFilter
{
    /**
     * @param list<string> $ratePlanCodes
     * @param list<string> $includedServiceIds
     * @param list<ChannelCode> $channelCodes
     * @param list<string> $promoCodes
     * @param list<string> $companyIds
     * @param list<string> $baseRatePlanIds
     * @param list<string> $unitGroupIds
     * @param list<string> $timeSliceDefinitionIds
     * @param list<UnitGroupType> $unitGroupTypes
     * @param list<GuaranteeType> $minGuaranteeTypes
     * @param list<string> $cancellationPolicyIds
     * @param list<string> $noShowPolicyIds
     * @param list<string> $derivationLevelFilter expressions like 'eq_1', 'lte_2' (operators: eq, neq, lt, gt, lte, gte), all of which must match
     */
    public function __construct(
        public ?string $propertyId = null,
        public array $ratePlanCodes = [],
        public array $includedServiceIds = [],
        public array $channelCodes = [],
        public array $promoCodes = [],
        public array $companyIds = [],
        public array $baseRatePlanIds = [],
        public array $unitGroupIds = [],
        public array $timeSliceDefinitionIds = [],
        public array $unitGroupTypes = [],
        public ?TimeSliceTemplate $timeSliceTemplate = null,
        public array $minGuaranteeTypes = [],
        public array $cancellationPolicyIds = [],
        public array $noShowPolicyIds = [],
        public ?bool $isDerived = null,
        public array $derivationLevelFilter = [],
        public ?bool $includeArchived = null,
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'ratePlanCodes' => implode(',', $this->ratePlanCodes) ?: null,
            'includedServiceIds' => implode(',', $this->includedServiceIds) ?: null,
            'channelCodes' => implode(',', array_map(static fn (ChannelCode $c): string => $c->value, $this->channelCodes)) ?: null,
            'promoCodes' => implode(',', $this->promoCodes) ?: null,
            'companyIds' => implode(',', $this->companyIds) ?: null,
            'baseRatePlanIds' => implode(',', $this->baseRatePlanIds) ?: null,
            'unitGroupIds' => implode(',', $this->unitGroupIds) ?: null,
            'timeSliceDefinitionIds' => implode(',', $this->timeSliceDefinitionIds) ?: null,
            'unitGroupTypes' => implode(',', array_map(static fn (UnitGroupType $t): string => $t->value, $this->unitGroupTypes)) ?: null,
            'timeSliceTemplate' => $this->timeSliceTemplate?->value,
            'minGuaranteeTypes' => implode(',', array_map(static fn (GuaranteeType $t): string => $t->value, $this->minGuaranteeTypes)) ?: null,
            'cancellationPolicyIds' => implode(',', $this->cancellationPolicyIds) ?: null,
            'noShowPolicyIds' => implode(',', $this->noShowPolicyIds) ?: null,
            'isDerived' => $this->isDerived,
            'derivationLevelFilter' => implode(',', $this->derivationLevelFilter) ?: null,
            'includeArchived' => $this->includeArchived,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
