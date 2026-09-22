<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Reports\DTO\ArrivalsReport;
use Oleksyuk\Apaleo\Resource\Reports\DTO\CompanyInvoice;
use Oleksyuk\Apaleo\Resource\Reports\DTO\OrderedService;
use Oleksyuk\Apaleo\Resource\Reports\DTO\PropertyPerformanceReport;
use Oleksyuk\Apaleo\Resource\Reports\DTO\RevenuesReportItem;
use Oleksyuk\Apaleo\Resource\Reports\Enum\TravelPurpose;
use Oleksyuk\Apaleo\Resource\Reports\Requests\GetArrivalsReportRequest;
use Oleksyuk\Apaleo\Resource\Reports\Requests\GetPropertyPerformanceReportRequest;
use Oleksyuk\Apaleo\Resource\Reports\Requests\GetRevenuesReportRequest;
use Oleksyuk\Apaleo\Resource\Reports\Requests\ListCompanyInvoicesVatRequest;
use Oleksyuk\Apaleo\Resource\Reports\Requests\ListOrderedServicesRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The Reports API (reports-v1) — five unrelated read-only reports, so no sub-resources beneath this one. */
final readonly class ReportsResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<string> $serviceIds
     *
     * @return PaginatedResult<OrderedService>
     */
    public function orderedServices(string $propertyId, array $serviceIds, \DateTimeImmutable $from, \DateTimeImmutable $to): PaginatedResult
    {
        $data = $this->pipeline->send(new ListOrderedServicesRequest($propertyId, $serviceIds, $from, $to));

        return new PaginatedResult(
            items: array_map(OrderedService::fromArray(...), ResponseData::nestedList($data, 'orderedServices')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function arrivals(string $propertyId, int $month, int $year): ArrivalsReport
    {
        $data = $this->pipeline->send(new GetArrivalsReportRequest($propertyId, $month, $year));

        return ArrivalsReport::fromArray($data);
    }

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
    public function propertyPerformance(
        string $propertyId,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        array $companyIds = [],
        array $ratePlanIds = [],
        array $unitGroupTypes = [],
        array $unitGroupIds = [],
        array $timeSliceDefinitionIds = [],
        array $channelCodes = [],
        array $sources = [],
        array $marketSegmentIds = [],
        ?TravelPurpose $travelPurpose = null,
        array $expand = [],
    ): PropertyPerformanceReport {
        $data = $this->pipeline->send(new GetPropertyPerformanceReportRequest(
            $propertyId,
            $from,
            $to,
            $companyIds,
            $ratePlanIds,
            $unitGroupTypes,
            $unitGroupIds,
            $timeSliceDefinitionIds,
            $channelCodes,
            $sources,
            $marketSegmentIds,
            $travelPurpose,
            $expand,
        ));

        return PropertyPerformanceReport::fromArray($data);
    }

    /**
     * @param list<string> $companyIds
     * @param list<string> $dateFilter expressions like "gte_2024-01-01", "lt_2024-02-01" (interval capped at 1 month by the API)
     *
     * @return PaginatedResult<CompanyInvoice>
     */
    public function companyInvoicesVat(string $propertyId, array $companyIds = [], array $dateFilter = []): PaginatedResult
    {
        $data = $this->pipeline->send(new ListCompanyInvoicesVatRequest($propertyId, $companyIds, $dateFilter));

        return new PaginatedResult(
            items: array_map(CompanyInvoice::fromArray(...), ResponseData::nestedList($data, 'companyInvoices')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function revenues(string $propertyId, \DateTimeImmutable $from, \DateTimeImmutable $to, ?string $languageCode = null): RevenuesReportItem
    {
        $data = $this->pipeline->send(new GetRevenuesReportRequest($propertyId, $from, $to, $languageCode));

        return RevenuesReportItem::fromArray($data);
    }
}
