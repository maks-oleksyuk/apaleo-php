<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\DTO\CreateUnitAttributeDefinition;
use Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\DTO\UnitAttributeDefinition;
use Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\Requests\CreateUnitAttributeRequest;
use Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\Requests\DeleteUnitAttributeRequest;
use Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\Requests\GetUnitAttributeRequest;
use Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\Requests\ListUnitAttributesRequest;
use Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\Requests\UpdateUnitAttributeRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class UnitAttributeResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function get(string $unitAttributeId): UnitAttributeDefinition
    {
        $data = $this->pipeline->send(new GetUnitAttributeRequest($unitAttributeId));

        return UnitAttributeDefinition::fromArray($data);
    }

    /**
     * @return PaginatedResult<UnitAttributeDefinition>
     */
    public function list(?int $pageNumber = null, ?int $pageSize = null): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListUnitAttributesRequest($pageNumber, $pageSize));

        return new PaginatedResult(
            items: array_map(UnitAttributeDefinition::fromArray(...), ResponseData::nestedList($data, 'unitAttributes')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function create(CreateUnitAttributeDefinition $data, ?string $idempotencyKey = null): string
    {
        $response = $this->pipeline->send(new CreateUnitAttributeRequest($data, $idempotencyKey));

        return ResponseData::string($response, 'id');
    }

    public function update(string $unitAttributeId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateUnitAttributeRequest($unitAttributeId, $patch));
    }

    public function delete(string $unitAttributeId): void
    {
        $this->pipeline->send(new DeleteUnitAttributeRequest($unitAttributeId));
    }
}
