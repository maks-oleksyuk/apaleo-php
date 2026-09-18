<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\CreateProperty;

final class CreatePropertyRequest extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        private readonly CreateProperty $data,
    ) {
    }

    public function endpoint(): string
    {
        return '/inventory/v1/properties';
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
