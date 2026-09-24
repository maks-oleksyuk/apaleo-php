<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit;

use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitArchiveFilter;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitCondition;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitMaintenanceType;

final readonly class UnitFilter
{
    /**
     * @param list<string> $unitGroupIds
     * @param list<string> $unitAttributeIds
     */
    public function __construct(
        public ?string $propertyId = null,
        public ?string $unitGroupId = null,
        public array $unitGroupIds = [],
        public array $unitAttributeIds = [],
        public ?bool $isOccupied = null,
        public ?UnitMaintenanceType $maintenanceType = null,
        public ?UnitCondition $condition = null,
        public ?string $textSearch = null,
        public ?UnitArchiveFilter $status = null,
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'unitGroupId' => $this->unitGroupId,
            'unitGroupIds' => implode(',', $this->unitGroupIds) ?: null,
            'unitAttributeIds' => implode(',', $this->unitAttributeIds) ?: null,
            'isOccupied' => $this->isOccupied,
            'maintenanceType' => $this->maintenanceType?->value,
            'condition' => $this->condition?->value,
            'textSearch' => $this->textSearch,
            'status' => $this->status?->value,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
