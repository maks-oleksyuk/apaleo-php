<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class DeleteUnitAttributeRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly string $unitAttributeId,
    ) {}

    public function endpoint(): string
    {
        return '/inventory/v1/unit-attributes/'.rawurlencode($this->unitAttributeId);
    }
}
