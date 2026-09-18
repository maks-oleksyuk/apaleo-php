<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitCondition;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Unit
{
    /**
     * @param array<string, string> $description localized, or ['default' => ...] when the endpoint returns a plain string
     * @param list<UnitAttribute> $attributes
     * @param list<ConnectedUnit> $connectedUnits
     */
    public function __construct(
        public string $id,
        public string $name,
        public array $description,
        public string $propertyId,
        public ?string $unitGroupId,
        public ?string $connectingUnitId,
        public int $maxPersons,
        public UnitCondition $condition,
        public string $rawCondition,
        public bool $isOccupied,
        public ?UnitMaintenance $maintenance,
        public bool $isArchived,
        public ?\DateTimeImmutable $archived,
        public array $attributes,
        public array $connectedUnits,
        public \DateTimeImmutable $created,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $status = ResponseData::nested($data, 'status');
        $property = ResponseData::nested($data, 'property');
        $unitGroup = ResponseData::nested($data, 'unitGroup');
        $connectingUnit = ResponseData::nested($data, 'connectingUnit');
        $maintenance = ResponseData::nested($status, 'maintenance');
        $archived = ResponseData::nullableString($data, 'archived');

        $condition = ResponseData::string($status, 'condition');

        return new self(
            id: ResponseData::string($data, 'id'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::localizedText($data, 'description'),
            propertyId: ResponseData::string($property, 'id'),
            unitGroupId: ResponseData::nullableString($unitGroup, 'id'),
            connectingUnitId: ResponseData::nullableString($connectingUnit, 'id'),
            maxPersons: ResponseData::int($data, 'maxPersons'),
            condition: UnitCondition::fromApi($condition),
            rawCondition: $condition,
            isOccupied: ResponseData::bool($status, 'isOccupied'),
            maintenance: $maintenance !== [] ? UnitMaintenance::fromArray($maintenance) : null,
            isArchived: ResponseData::bool($data, 'isArchived'),
            archived: $archived !== null ? new \DateTimeImmutable($archived) : null,
            attributes: array_map(
                UnitAttribute::fromArray(...),
                ResponseData::nestedList($data, 'attributes'),
            ),
            connectedUnits: array_map(
                ConnectedUnit::fromArray(...),
                ResponseData::nestedList($data, 'connectedUnits'),
            ),
            created: new \DateTimeImmutable(ResponseData::string($data, 'created')),
        );
    }
}
