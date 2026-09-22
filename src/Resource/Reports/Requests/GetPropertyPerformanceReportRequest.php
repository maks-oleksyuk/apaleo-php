<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Availability\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Reports\Enum\TravelPurpose;

final readonly class GetPropertyPerformanceReportRequest extends Request
{
    /**
     * @param list<string>        $companyIds
     * @param list<string>        $ratePlanIds
     * @param list<UnitGroupType> $unitGroupTypes
     * @param list<string>        $unitGroupIds
     * @param list<string>        $timeSliceDefinitionIds
     * @param list<ChannelCode>   $channelCodes
     * @param list<string>        $sources
     * @param list<string>        $marketSegmentIds
     * @param list<'businessDays'>        $expand
     */
    public function __construct(
        private string $propertyId,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to,
        private array $companyIds = [],
        private array $ratePlanIds = [],
        private array $unitGroupTypes = [],
        private array $unitGroupIds = [],
        private array $timeSliceDefinitionIds = [],
        private array $channelCodes = [],
        private array $sources = [],
        private array $marketSegmentIds = [],
        private ?TravelPurpose $travelPurpose = null,
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/reports/v1/reports/property-performance';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'companyIds' => implode(',', $this->companyIds) ?: null,
            'ratePlanIds' => implode(',', $this->ratePlanIds) ?: null,
            'unitGroupTypes' => implode(',', array_map(static fn (UnitGroupType $t): string => $t->value, $this->unitGroupTypes)) ?: null,
            'unitGroupIds' => implode(',', $this->unitGroupIds) ?: null,
            'timeSliceDefinitionIds' => implode(',', $this->timeSliceDefinitionIds) ?: null,
            'channelCodes' => implode(',', array_map(static fn (ChannelCode $c): string => $c->value, $this->channelCodes)) ?: null,
            'sources' => implode(',', $this->sources) ?: null,
            'marketSegmentIds' => implode(',', $this->marketSegmentIds) ?: null,
            'travelPurpose' => $this->travelPurpose?->value,
            'expand' => implode(',', $this->expand) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
