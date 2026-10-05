<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Service;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\Service\DTO\ServiceAvailabilityTimeSlice;
use Oleksyuk\Apaleo\Resource\Availability\Service\Requests\ListServiceAvailabilityRequest;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;

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
     *
     * @throws ApaleoExceptionInterface
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

        return PaginatedResult::fromResponse($data, 'timeSlices', ServiceAvailabilityTimeSlice::fromArray(...));
    }
}
