<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitCondition;

/** One entry of OperationsResource::setUnitsCondition()'s bulk request. */
final readonly class UnitConditionUpdate
{
    public function __construct(
        public string $unitId,
        public UnitCondition $condition,
    ) {}

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'id' => $this->unitId,
            'condition' => $this->condition->value,
        ];
    }
}
