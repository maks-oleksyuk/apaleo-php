<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitArchiveFilter;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitCondition;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitMaintenanceType;

final class ListUnitsRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param list<string> $unitGroupIds
     * @param list<string> $unitAttributeIds
     * @param list<string> $expand supported: property, unitGroup, connectedUnits, actions
     */
    public function __construct(
        private readonly ?string $propertyId = null,
        private readonly ?string $unitGroupId = null,
        private readonly array $unitGroupIds = [],
        private readonly array $unitAttributeIds = [],
        private readonly ?bool $isOccupied = null,
        private readonly ?UnitMaintenanceType $maintenanceType = null,
        private readonly ?UnitCondition $condition = null,
        private readonly ?string $textSearch = null,
        private readonly ?UnitArchiveFilter $status = null,
        private readonly ?int $pageNumber = null,
        private readonly ?int $pageSize = null,
        private readonly array $expand = [],
    ) {
    }

    public function endpoint(): string
    {
        return '/inventory/v1/units';
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
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
            'expand' => implode(',', $this->expand) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
