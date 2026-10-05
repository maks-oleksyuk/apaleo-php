<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Unit;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\Unit\DTO\AvailableUnitItem;
use Oleksyuk\Apaleo\Resource\Availability\Unit\Requests\ListAvailableUnitsRequest;
use Oleksyuk\Apaleo\Resource\Availability\Unit\Requests\ListReservationAvailableUnitsRequest;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitCondition;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;

final readonly class UnitResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<string> $unitAttributeIds
     *
     * @return PaginatedResult<AvailableUnitItem>
     *
     * @throws ApaleoExceptionInterface
     */
    public function list(
        string $propertyId,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        ?string $unitGroupId = null,
        ?bool $includeOutOfService = null,
        ?UnitCondition $unitCondition = null,
        array $unitAttributeIds = [],
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListAvailableUnitsRequest(
            $propertyId,
            $from,
            $to,
            $unitGroupId,
            $includeOutOfService,
            $unitCondition,
            $unitAttributeIds,
            $pageNumber,
            $pageSize,
        ));

        return PaginatedResult::fromResponse($data, 'units', AvailableUnitItem::fromArray(...));
    }

    /**
     * @param list<string> $unitAttributeIds
     *
     * @return PaginatedResult<AvailableUnitItem>
     *
     * @throws ApaleoExceptionInterface
     */
    public function forReservation(
        string $reservationId,
        ?string $unitGroupId = null,
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null,
        ?bool $includeOutOfService = null,
        ?UnitCondition $unitCondition = null,
        array $unitAttributeIds = [],
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListReservationAvailableUnitsRequest(
            $reservationId,
            $unitGroupId,
            $from,
            $to,
            $includeOutOfService,
            $unitCondition,
            $unitAttributeIds,
            $pageNumber,
            $pageSize,
        ));

        return PaginatedResult::fromResponse($data, 'units', AvailableUnitItem::fromArray(...));
    }
}
