<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateUnitAttributeRequest extends Request
{
    public function __construct(
        private string $unitAttributeId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/unit-attributes/'.rawurlencode($this->unitAttributeId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
