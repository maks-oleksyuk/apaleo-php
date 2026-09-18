<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class ResetPropertyRequest extends Request
{
    protected Method $method = Method::PUT;

    public function __construct(
        private readonly string $propertyId,
    ) {}

    public function endpoint(): string
    {
        return '/inventory/v1/property-actions/'.rawurlencode($this->propertyId).'/reset';
    }
}
