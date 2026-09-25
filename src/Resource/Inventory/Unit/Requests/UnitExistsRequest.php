<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UnitExistsRequest extends Request
{
    public function __construct(
        private string $unitId,
    ) {}

    public function method(): Method
    {
        return Method::HEAD;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/units/'.rawurlencode($this->unitId);
    }
}
