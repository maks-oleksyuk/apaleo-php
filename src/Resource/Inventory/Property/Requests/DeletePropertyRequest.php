<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class DeletePropertyRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(
        private readonly string $propertyId,
    ) {
    }

    public function endpoint(): string
    {
        return "/inventory/v1/properties/{$this->propertyId}";
    }
}
