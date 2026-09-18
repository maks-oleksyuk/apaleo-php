<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class ResetPropertyRequest extends Request
{
    public function __construct(
        private readonly string $propertyId,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/property-actions/'.rawurlencode($this->propertyId).'/reset';
    }
}
