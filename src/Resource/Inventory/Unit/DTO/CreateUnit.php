<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitCondition;

final readonly class CreateUnit
{
    /**
     * @param array<string, string> $description localized, e.g. ['en' => 'Double Room']
     * @param list<string> $attributeIds
     * @param list<string> $connectedUnitIds
     */
    public function __construct(
        public string $propertyId,
        public string $name,
        public array $description,
        public int $maxPersons,
        public ?string $unitGroupId = null,
        public ?UnitCondition $condition = null,
        public array $attributeIds = [],
        public array $connectedUnitIds = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'name' => $this->name,
            'description' => $this->description,
            'unitGroupId' => $this->unitGroupId,
            'maxPersons' => $this->maxPersons,
            'condition' => $this->condition?->value,
            'attributes' => array_map(static fn (string $id): array => ['id' => $id], $this->attributeIds) ?: null,
            'connectedUnits' => array_map(static fn (string $id): array => ['unitId' => $id], $this->connectedUnitIds) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
