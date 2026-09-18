<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class ArchiveUnitRequest extends Request
{
    public function __construct(
        private readonly string $unitId,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/unit-actions/'.rawurlencode($this->unitId).'/archive';
    }
}
