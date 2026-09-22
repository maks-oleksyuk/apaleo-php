<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitArchiveFilter;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitCondition;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitMaintenanceType;

final readonly class CountUnitsRequest extends Request
{
    /**
     * @param list<string> $unitGroupIds
     * @param list<string> $unitAttributeIds
     */
    public function __construct(
        private ?string $propertyId = null,
        private ?string $unitGroupId = null,
        private array $unitGroupIds = [],
        private array $unitAttributeIds = [],
        private ?bool $isOccupied = null,
        private ?UnitMaintenanceType $maintenanceType = null,
        private ?UnitCondition $condition = null,
        private ?string $textSearch = null,
        private ?UnitArchiveFilter $status = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/units/$count';
    }

    public function query(): array
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
