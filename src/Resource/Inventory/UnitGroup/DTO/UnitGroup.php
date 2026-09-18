<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class UnitGroup
{
    /**
     * @param array<string, string> $name localized, or ['default' => ...] when the endpoint returns a plain string
     * @param array<string, string> $description
     * @param list<ConnectedUnitGroup> $connectedUnitGroups
     */
    public function __construct(
        public string $id,
        public string $code,
        public string $propertyId,
        public array $name,
        public array $description,
        public int $memberCount,
        public ?int $maxPersons,
        public ?int $rank,
        public UnitGroupType $type,
        public string $rawType,
        public array $connectedUnitGroups,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $property = ResponseData::nested($data, 'property');
        $type = ResponseData::string($data, 'type');

        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            propertyId: ResponseData::string($property, 'id'),
            name: ResponseData::localizedText($data, 'name'),
            description: ResponseData::localizedText($data, 'description'),
            memberCount: ResponseData::int($data, 'memberCount'),
            maxPersons: ResponseData::nullableInt($data, 'maxPersons'),
            rank: ResponseData::nullableInt($data, 'rank'),
            type: UnitGroupType::fromApi($type),
            rawType: $type,
            connectedUnitGroups: array_map(
                ConnectedUnitGroup::fromArray(...),
                ResponseData::nestedList($data, 'connectedUnitGroups'),
            ),
        );
    }
}
