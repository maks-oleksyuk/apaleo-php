<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Routing\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class DeleteRoutingRequest extends Request
{
    public function __construct(
        private string $routingId,
    ) {}

    public function method(): Method
    {
        return Method::DELETE;
    }

    public function endpoint(): string
    {
        return '/finance/v1/routings/'.rawurlencode($this->routingId);
    }
}
