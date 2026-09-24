<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitCondition;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitMaintenanceType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class UnitListItem
{
    /**
     * @param list<UnitAttribute> $attributes
     * @param list<ConnectedUnit> $connectedUnits
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $propertyId,
        public ?string $unitGroupId,
        public ?string $connectingUnitId,
        public int $maxPersons,
        public UnitCondition $condition,
        public string $rawCondition,
        public bool $isOccupied,
        public ?UnitMaintenanceType $maintenanceType,
        public bool $isArchived,
        public ?\DateTimeImmutable $archived,
        public array $attributes,
        public array $connectedUnits,
        public \DateTimeImmutable $created,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $status = ResponseData::nested($data, 'status');
        $maintenanceType = ResponseData::nullableString(ResponseData::nested($status, 'maintenance'), 'type');
        $condition = ResponseData::string($status, 'condition');

        return new self(
            id: ResponseData::string($data, 'id'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::string($data, 'description'),
            propertyId: ResponseData::string(ResponseData::nested($data, 'property'), 'id'),
            unitGroupId: ResponseData::nullableString(ResponseData::nested($data, 'unitGroup'), 'id'),
            connectingUnitId: ResponseData::nullableString(ResponseData::nested($data, 'connectingUnit'), 'id'),
            maxPersons: ResponseData::int($data, 'maxPersons'),
            condition: UnitCondition::fromApi($condition),
            rawCondition: $condition,
            isOccupied: ResponseData::bool($status, 'isOccupied'),
            maintenanceType: $maintenanceType !== null ? UnitMaintenanceType::fromApi($maintenanceType) : null,
            isArchived: ResponseData::bool($data, 'isArchived'),
            archived: ResponseData::nullableDateTime($data, 'archived'),
            attributes: array_map(UnitAttribute::fromArray(...), ResponseData::nestedList($data, 'attributes')),
            connectedUnits: array_map(ConnectedUnit::fromArray(...), ResponseData::nestedList($data, 'connectedUnits')),
            created: ResponseData::dateTime($data, 'created'),
        );
    }
}
