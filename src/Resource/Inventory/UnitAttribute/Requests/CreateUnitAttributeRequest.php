<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\DTO\CreateUnitAttributeDefinition;

final class CreateUnitAttributeRequest extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        private readonly CreateUnitAttributeDefinition $data,
    ) {
    }

    public function endpoint(): string
    {
        return '/inventory/v1/unit-attributes';
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
