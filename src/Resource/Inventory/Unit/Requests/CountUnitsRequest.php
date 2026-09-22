<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\UnitFilter;

final readonly class CountUnitsRequest extends Request
{
    public function __construct(
        private UnitFilter $filter = new UnitFilter(),
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
        return $this->filter->toQuery();
    }
}
