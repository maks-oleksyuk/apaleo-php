<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TravelPurpose;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\Query;

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
            'companyIds' => Query::csv($this->companyIds),
            'ratePlanIds' => Query::csv($this->ratePlanIds),
            'unitGroupTypes' => Query::csv($this->unitGroupTypes),
            'unitGroupIds' => Query::csv($this->unitGroupIds),
            'timeSliceDefinitionIds' => Query::csv($this->timeSliceDefinitionIds),
            'channelCodes' => Query::csv($this->channelCodes),
            'sources' => Query::csv($this->sources),
            'marketSegmentIds' => Query::csv($this->marketSegmentIds),
            'travelPurpose' => $this->travelPurpose?->value,
            'expand' => Query::csv($this->expand),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
