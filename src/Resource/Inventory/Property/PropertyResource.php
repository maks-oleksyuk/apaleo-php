<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\CreateProperty;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\Property;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Enum\PropertyStatus;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\ArchivePropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\ClonePropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\CountPropertiesRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\CreatePropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\DeletePropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\GetPropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\ListPropertiesRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\ResetPropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\SetPropertyLiveRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\UpdatePropertyRequest;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PropertyResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function get(string $propertyId): Property
    {
        $data = $this->pipeline->send(new GetPropertyRequest($propertyId));

        return Property::fromArray($data);
    }

    /**
     * @param list<PropertyStatus> $status
     * @param list<string> $countryCode ISO Alpha-2 country codes
     * @param list<string> $expand supported: actions
     *
     * @return list<Property>
     */
    public function list(
        array $status = [],
        ?bool $includeArchived = null,
        array $countryCode = [],
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $expand = [],
    ): array {
        $data = $this->pipeline->send(new ListPropertiesRequest($status, $includeArchived, $countryCode, $pageNumber, $pageSize, $expand));

        return array_map(
            Property::fromArray(...),
            ResponseData::nestedList($data, 'properties'),
        );
    }

    public function count(): int
    {
        $data = $this->pipeline->send(new CountPropertiesRequest());

        return ResponseData::int($data, 'count');
    }

    /**
     * @return string the id of the created property
     */
    public function create(CreateProperty $data): string
    {
        $response = $this->pipeline->send(new CreatePropertyRequest($data));

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

    /**
     * @return string the id of the cloned property
     */
    public function clone(string $propertyId, CreateProperty $overrides): string
    {
        $response = $this->pipeline->send(new ClonePropertyRequest($propertyId, $overrides));

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
