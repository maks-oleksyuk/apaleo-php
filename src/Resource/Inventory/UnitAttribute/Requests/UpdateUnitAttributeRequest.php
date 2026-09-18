<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final class UpdateUnitAttributeRequest extends Request
{
    protected Method $method = Method::PATCH;

    public function __construct(
        private readonly string $unitAttributeId,
        private readonly JsonPatch $patch,
    ) {}

    public function endpoint(): string
    {
        return '/inventory/v1/unit-attributes/'.rawurlencode($this->unitAttributeId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
