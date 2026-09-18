<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\CreateProperty;

final class ClonePropertyRequest extends Request
{
    public function __construct(
        private readonly string $propertyId,
        private readonly CreateProperty $overrides,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/property-actions/'.rawurlencode($this->propertyId).'/clone';
    }

    public function body(): array
    {
        return $this->overrides->toArray();
    }
}
