<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\UnitGroup;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Resource\Availability\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Resource\Availability\UnitGroup\DTO\UnitGroupAvailabilityTimeSlice;
use Oleksyuk\Apaleo\Resource\Availability\UnitGroup\Requests\ListUnitGroupAvailabilityRequest;
use Oleksyuk\Apaleo\Resource\Availability\UnitGroup\Requests\UpdateUnitGroupAvailabilityRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class UnitGroupResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<UnitGroupType> $unitGroupTypes
     * @param list<string>        $timeSliceDefinitionIds
     * @param list<string>        $unitGroupIds
     * @param list<int>           $childrenAges
     *
     * @return PaginatedResult<UnitGroupAvailabilityTimeSlice>
     */
    public function list(
        string $propertyId,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        ?TimeSliceTemplate $timeSliceTemplate = null,
        array $unitGroupTypes = [],
        array $timeSliceDefinitionIds = [],
        array $unitGroupIds = [],
        ?int $adults = null,
        array $childrenAges = [],
        ?bool $onlySellable = null,
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListUnitGroupAvailabilityRequest(
            $propertyId,
            $from,
            $to,
            $timeSliceTemplate,
            $unitGroupTypes,
            $timeSliceDefinitionIds,
            $unitGroupIds,
            $adults,
            $childrenAges,
            $onlySellable,
            $pageNumber,
            $pageSize,
        ));

        return new PaginatedResult(
            items: array_map(UnitGroupAvailabilityTimeSlice::fromArray(...), ResponseData::nestedList($data, 'timeSlices')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    /** Replaces the allowed overbooking count for a unit group in [$from, $to) — e.g. `(new JsonPatch())->replace('/allowedOverbookingCount', 2)`. */
    public function update(
        string $unitGroupId,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        TimeSliceTemplate $timeSliceTemplate,
        JsonPatch $patch,
    ): void {
        $this->pipeline->send(new UpdateUnitGroupAvailabilityRequest($unitGroupId, $from, $to, $timeSliceTemplate, $patch));
    }
}
