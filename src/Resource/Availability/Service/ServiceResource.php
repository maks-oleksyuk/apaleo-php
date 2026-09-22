<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Service;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\Service\DTO\ServiceAvailabilityTimeSlice;
use Oleksyuk\Apaleo\Resource\Availability\Service\Requests\ListServiceAvailabilityRequest;
use Oleksyuk\Apaleo\Resource\Availability\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ServiceResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<string> $timeSliceDefinitionIds
     * @param list<string> $channelCodes
     *
     * @return PaginatedResult<ServiceAvailabilityTimeSlice>
     */
    public function list(
        string $propertyId,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        ?TimeSliceTemplate $timeSliceTemplate = null,
        array $timeSliceDefinitionIds = [],
        array $channelCodes = [],
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListServiceAvailabilityRequest(
            $propertyId,
            $from,
            $to,
            $timeSliceTemplate,
            $timeSliceDefinitionIds,
            $channelCodes,
            $pageNumber,
            $pageSize,
        ));

        return new PaginatedResult(
            items: array_map(ServiceAvailabilityTimeSlice::fromArray(...), ResponseData::nestedList($data, 'timeSlices')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }
}
