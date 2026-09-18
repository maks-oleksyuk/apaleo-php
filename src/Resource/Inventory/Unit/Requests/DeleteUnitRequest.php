<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class DeleteUnitRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly string $unitId,
    ) {}

    public function endpoint(): string
    {
        return '/inventory/v1/units/'.rawurlencode($this->unitId);
    }
}
