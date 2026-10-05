<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Reports\DTO\ArrivalsReport;
use Oleksyuk\Apaleo\Resource\Reports\DTO\CompanyInvoice;
use Oleksyuk\Apaleo\Resource\Reports\DTO\OrderedService;
use Oleksyuk\Apaleo\Resource\Reports\DTO\PropertyPerformanceReport;
use Oleksyuk\Apaleo\Resource\Reports\DTO\RevenuesReportItem;
use Oleksyuk\Apaleo\Resource\Reports\Requests\GetArrivalsReportRequest;
use Oleksyuk\Apaleo\Resource\Reports\Requests\GetPropertyPerformanceReportRequest;
use Oleksyuk\Apaleo\Resource\Reports\Requests\GetRevenuesReportRequest;
use Oleksyuk\Apaleo\Resource\Reports\Requests\ListCompanyInvoicesVatRequest;
use Oleksyuk\Apaleo\Resource\Reports\Requests\ListOrderedServicesRequest;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TravelPurpose;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\PaginatedResult;

final readonly class ReportsResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<string> $serviceIds
     *
     * @return PaginatedResult<OrderedService>
     *
     * @throws ApaleoExceptionInterface
     */
    public function orderedServices(string $propertyId, array $serviceIds, \DateTimeImmutable $from, \DateTimeImmutable $to): PaginatedResult
    {
        $data = $this->pipeline->send(new ListOrderedServicesRequest($propertyId, $serviceIds, $from, $to));

        return PaginatedResult::fromResponse($data, 'orderedServices', OrderedService::fromArray(...));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
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
     *
     * @throws ApaleoExceptionInterface
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
     *
     * @throws ApaleoExceptionInterface
     */
    public function companyInvoicesVat(string $propertyId, array $companyIds = [], array $dateFilter = []): PaginatedResult
    {
        $data = $this->pipeline->send(new ListCompanyInvoicesVatRequest($propertyId, $companyIds, $dateFilter));

        return PaginatedResult::fromResponse($data, 'companyInvoices', CompanyInvoice::fromArray(...));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function revenues(string $propertyId, \DateTimeImmutable $from, \DateTimeImmutable $to, ?string $languageCode = null): RevenuesReportItem
    {
        $data = $this->pipeline->send(new GetRevenuesReportRequest($propertyId, $from, $to, $languageCode));

        return RevenuesReportItem::fromArray($data);
    }
}
