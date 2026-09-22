<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup;

use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Enum\UnitGroupType;

/** Filter criteria shared by UnitGroupResource::list() and ::count(). */
final readonly class UnitGroupFilter
{
    /** @param list<UnitGroupType> $unitGroupTypes */
    public function __construct(
        public ?string $propertyId = null,
        public array $unitGroupTypes = [],
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'unitGroupTypes' => implode(',', array_map(static fn (UnitGroupType $t): string => $t->value, $this->unitGroupTypes)) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
