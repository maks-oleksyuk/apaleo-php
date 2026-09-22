<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\DTO\CreateUnit;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\DTO\Unit;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests\ArchiveUnitRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests\BulkCreateUnitsRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests\BulkUpdateUnitsRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests\CountUnitsRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests\CreateUnitRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests\DeleteUnitRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests\GetUnitRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests\ListUnitsRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests\UpdateUnitRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class UnitResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function get(string $unitId): Unit
    {
        $data = $this->pipeline->send(new GetUnitRequest($unitId));

        return Unit::fromArray($data);
    }

    /**
     * @param list<'actions'|'connectedUnits'|'property'|'unitGroup'> $expand
     *
     * @return PaginatedResult<Unit>
     */
    public function list(
        UnitFilter $filter = new UnitFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListUnitsRequest($filter, $pageNumber, $pageSize, $expand));

        return new PaginatedResult(
            items: array_map(Unit::fromArray(...), ResponseData::nestedList($data, 'units')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function count(UnitFilter $filter = new UnitFilter()): int
    {
        $data = $this->pipeline->send(new CountUnitsRequest($filter));

        return ResponseData::int($data, 'count');
    }

    /**
     * @return string the id of the created unit
     */
    public function create(CreateUnit $data): string
    {
        $response = $this->pipeline->send(new CreateUnitRequest($data));

        return ResponseData::string($response, 'id');
    }

    /**
     * @param list<CreateUnit> $units
     *
     * @return list<string> ids of the created units
     */
    public function bulkCreate(array $units): array
    {
        $response = $this->pipeline->send(new BulkCreateUnitsRequest($units));

        return ResponseData::stringList($response, 'ids');
    }

    public function update(string $unitId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateUnitRequest($unitId, $patch));
    }

    /**
     * @param list<string> $unitIds
     */
    public function bulkUpdate(array $unitIds, JsonPatch $patch): void
    {
        $this->pipeline->send(new BulkUpdateUnitsRequest($unitIds, $patch));
    }

    public function delete(string $unitId): void
    {
        $this->pipeline->send(new DeleteUnitRequest($unitId));
    }

    public function archive(string $unitId): void
    {
        $this->pipeline->send(new ArchiveUnitRequest($unitId));
    }
}
