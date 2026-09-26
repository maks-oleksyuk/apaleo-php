<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup;

use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\Query;

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
            'unitGroupTypes' => Query::csv($this->unitGroupTypes),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
