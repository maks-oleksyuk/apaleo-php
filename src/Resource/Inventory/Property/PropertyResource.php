<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property;

use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\CreateProperty;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\Property;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\PropertyListItem;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\ArchivePropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\ClonePropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\CountPropertiesRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\CreatePropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\DeletePropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\GetPropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\ListPropertiesRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\PropertyExistsRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\ResetPropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\SetPropertyLiveRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\UpdatePropertyRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PropertyResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @param ?list<string> $languages */
    public function get(string $propertyId, ?array $languages = null): Property
    {
        $data = $this->pipeline->send(new GetPropertyRequest($propertyId, $languages));

        return Property::fromArray($data);
    }

    public function exists(string $propertyId): bool
    {
        try {
            $this->pipeline->send(new PropertyExistsRequest($propertyId));

            return true;
        } catch (ApaleoNotFoundException) {
            return false;
        }
    }

    /**
     * @param list<'actions'> $expand
     *
     * @return PaginatedResult<PropertyListItem>
     */
    public function list(
        PropertyFilter $filter = new PropertyFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListPropertiesRequest($filter, $pageNumber, $pageSize, $expand));

        return new PaginatedResult(
            items: array_map(PropertyListItem::fromArray(...), ResponseData::nestedList($data, 'properties')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function count(): int
    {
        $data = $this->pipeline->send(new CountPropertiesRequest());

        return ResponseData::int($data, 'count');
    }

    public function create(CreateProperty $data, ?string $idempotencyKey = null): string
    {
        $response = $this->pipeline->send(new CreatePropertyRequest($data, $idempotencyKey));

        return ResponseData::string($response, 'id');
    }

    public function update(string $propertyId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdatePropertyRequest($propertyId, $patch));
    }

    public function delete(string $propertyId): void
    {
        $this->pipeline->send(new DeletePropertyRequest($propertyId));
    }

    public function clone(string $propertyId, CreateProperty $overrides, ?string $idempotencyKey = null): string
    {
        $response = $this->pipeline->send(new ClonePropertyRequest($propertyId, $overrides, $idempotencyKey));

        return ResponseData::string($response, 'id');
    }

    public function archive(string $propertyId): void
    {
        $this->pipeline->send(new ArchivePropertyRequest($propertyId));
    }

    public function setLive(string $propertyId): void
    {
        $this->pipeline->send(new SetPropertyLiveRequest($propertyId));
    }

    public function reset(string $propertyId): void
    {
        $this->pipeline->send(new ResetPropertyRequest($propertyId));
    }
}
