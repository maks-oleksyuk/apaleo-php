<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final class UpdatePropertyRequest extends Request
{
    protected Method $method = Method::PATCH;

    public function __construct(
        private readonly string $propertyId,
        private readonly JsonPatch $patch,
    ) {}

    public function endpoint(): string
    {
        return '/inventory/v1/properties/'.rawurlencode($this->propertyId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
