<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UnitGroupExistsRequest extends Request
{
    public function __construct(
        private string $unitGroupId,
    ) {}

    public function method(): Method
    {
        return Method::HEAD;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/unit-groups/'.rawurlencode($this->unitGroupId);
    }
}
