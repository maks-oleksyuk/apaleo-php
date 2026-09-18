<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO\ReplaceUnitGroup;

final class ReplaceUnitGroupRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly string $unitGroupId,
        private readonly ReplaceUnitGroup $data,
    ) {
    }

    public function endpoint(): string
    {
        return "/inventory/v1/unit-groups/{$this->unitGroupId}";
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
