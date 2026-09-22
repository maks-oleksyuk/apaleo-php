<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO\CreateUnitGroup;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO\ReplaceUnitGroup;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO\UnitGroup;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests\CountUnitGroupsRequest;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests\CreateUnitGroupRequest;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests\DeleteUnitGroupRequest;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests\GetUnitGroupRequest;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests\ListUnitGroupsRequest;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests\ReplaceUnitGroupRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class UnitGroupResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function get(string $unitGroupId): UnitGroup
    {
        $data = $this->pipeline->send(new GetUnitGroupRequest($unitGroupId));

        return UnitGroup::fromArray($data);
    }

    /**
     * @param list<'connectedUnitGroups'|'property'> $expand
     *
     * @return PaginatedResult<UnitGroup>
     */
    public function list(
        UnitGroupFilter $filter = new UnitGroupFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListUnitGroupsRequest($filter, $pageNumber, $pageSize, $expand));

        return new PaginatedResult(
            items: array_map(UnitGroup::fromArray(...), ResponseData::nestedList($data, 'unitGroups')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function count(UnitGroupFilter $filter = new UnitGroupFilter()): int
    {
        $data = $this->pipeline->send(new CountUnitGroupsRequest($filter));

        return ResponseData::int($data, 'count');
    }

    /**
     * @return string the id of the created unit group
     */
    public function create(CreateUnitGroup $data): string
    {
        $response = $this->pipeline->send(new CreateUnitGroupRequest($data));

        return ResponseData::string($response, 'id');
    }

    public function replace(string $unitGroupId, ReplaceUnitGroup $data): void
    {
        $this->pipeline->send(new ReplaceUnitGroupRequest($unitGroupId, $data));
    }

    public function delete(string $unitGroupId): void
    {
        $this->pipeline->send(new DeleteUnitGroupRequest($unitGroupId));
    }
}
