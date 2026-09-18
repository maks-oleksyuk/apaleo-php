<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final class UpdateUnitRequest extends Request
{
    protected Method $method = Method::PATCH;

    public function __construct(
        private readonly string $unitId,
        private readonly JsonPatch $patch,
    ) {}

    public function endpoint(): string
    {
        return '/inventory/v1/units/'.rawurlencode($this->unitId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
