<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Enum\UnitGroupType;

final readonly class CountUnitGroupsRequest extends Request
{
    /**
     * @param list<UnitGroupType> $unitGroupTypes
     */
    public function __construct(
        private ?string $propertyId = null,
        private array $unitGroupTypes = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/unit-groups/$count';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'unitGroupTypes' => implode(',', array_map(
                static fn (UnitGroupType $t): string => $t->value,
                $this->unitGroupTypes,
            )) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
