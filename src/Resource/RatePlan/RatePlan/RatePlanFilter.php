<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan;

use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\Query;

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
            'ratePlanCodes' => Query::csv($this->ratePlanCodes),
            'includedServiceIds' => Query::csv($this->includedServiceIds),
            'channelCodes' => Query::csv($this->channelCodes),
            'promoCodes' => Query::csv($this->promoCodes),
            'companyIds' => Query::csv($this->companyIds),
            'baseRatePlanIds' => Query::csv($this->baseRatePlanIds),
            'unitGroupIds' => Query::csv($this->unitGroupIds),
            'timeSliceDefinitionIds' => Query::csv($this->timeSliceDefinitionIds),
            'unitGroupTypes' => Query::csv($this->unitGroupTypes),
            'timeSliceTemplate' => $this->timeSliceTemplate?->value,
            'minGuaranteeTypes' => Query::csv($this->minGuaranteeTypes),
            'cancellationPolicyIds' => Query::csv($this->cancellationPolicyIds),
            'noShowPolicyIds' => Query::csv($this->noShowPolicyIds),
            'isDerived' => $this->isDerived,
            'derivationLevelFilter' => Query::csv($this->derivationLevelFilter),
            'includeArchived' => $this->includeArchived,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
