<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\UnitGroupFilter;

final readonly class CountUnitGroupsRequest extends Request
{
    public function __construct(
        private UnitGroupFilter $filter = new UnitGroupFilter(),
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
        return $this->filter->toQuery();
    }
}
