<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class ArchiveUnitRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly string $unitId,
    ) {
    }

    public function endpoint(): string
    {
        return "/inventory/v1/unit-actions/{$this->unitId}/archive";
    }
}
