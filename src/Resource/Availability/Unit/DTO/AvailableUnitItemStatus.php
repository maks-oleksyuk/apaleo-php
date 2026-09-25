<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Unit\DTO;

use Oleksyuk\Apaleo\Resource\Shared\Enum\MaintenanceType;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitCondition;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class AvailableUnitItemStatus
{
    public function __construct(
        public bool $isOccupied,
        public UnitCondition $condition,
        public ?MaintenanceType $maintenanceType,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $maintenanceType = ResponseData::nullableString($data, 'maintenanceType');

        return new self(
            isOccupied: ResponseData::bool($data, 'isOccupied'),
            condition: UnitCondition::fromApi(ResponseData::string($data, 'condition')),
            maintenanceType: $maintenanceType !== null ? MaintenanceType::fromApi($maintenanceType) : null,
        );
    }
}
