<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO\CreateUnitGroup;

final class CreateUnitGroupRequest extends Request
{
    public function __construct(
        private readonly CreateUnitGroup $data,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/unit-groups';
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
