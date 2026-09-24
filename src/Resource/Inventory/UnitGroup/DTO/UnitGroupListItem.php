<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class UnitGroupListItem
{
    /** @param list<ConnectedUnitGroup> $connectedUnitGroups */
    public function __construct(
        public string $id,
        public string $code,
        public string $propertyId,
        public string $name,
        public string $description,
        public int $memberCount,
        public ?int $maxPersons,
        public ?int $rank,
        public UnitGroupType $type,
        public string $rawType,
        public array $connectedUnitGroups,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $type = ResponseData::string($data, 'type');

        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            propertyId: ResponseData::string(ResponseData::nested($data, 'property'), 'id'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::string($data, 'description'),
            memberCount: ResponseData::int($data, 'memberCount'),
            maxPersons: ResponseData::nullableInt($data, 'maxPersons'),
            rank: ResponseData::nullableInt($data, 'rank'),
            type: UnitGroupType::fromApi($type),
            rawType: $type,
            connectedUnitGroups: array_map(ConnectedUnitGroup::fromArray(...), ResponseData::nestedList($data, 'connectedUnitGroups')),
        );
    }
}
