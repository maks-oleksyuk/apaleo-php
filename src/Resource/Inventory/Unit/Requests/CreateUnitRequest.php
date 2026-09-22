<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\DTO\CreateUnit;

final readonly class CreateUnitRequest extends Request
{
    public function __construct(
        private CreateUnit $data,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/inventory/v1/units';
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
