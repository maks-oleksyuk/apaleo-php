<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO\ReplaceUnitGroup;

final class ReplaceUnitGroupRequest extends Request
{
    public function __construct(
        private readonly string $unitGroupId,
        private readonly ReplaceUnitGroup $data,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/unit-groups/'.rawurlencode($this->unitGroupId);
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
