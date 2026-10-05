<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\UnitGroup;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\UnitGroup\DTO\UnitGroupAvailabilityTimeSlice;
use Oleksyuk\Apaleo\Resource\Availability\UnitGroup\Requests\ListUnitGroupAvailabilityRequest;
use Oleksyuk\Apaleo\Resource\Availability\UnitGroup\Requests\UpdateUnitGroupAvailabilityRequest;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;

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
     *
     * @throws ApaleoExceptionInterface
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

        return PaginatedResult::fromResponse($data, 'timeSlices', UnitGroupAvailabilityTimeSlice::fromArray(...));
    }

    /**
     * Replaces the allowed overbooking count for a unit group in [$from, $to) — e.g. `(new JsonPatch())->replace('/allowedOverbookingCount', 2)`.
     *
     * @throws ApaleoExceptionInterface
     */
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
