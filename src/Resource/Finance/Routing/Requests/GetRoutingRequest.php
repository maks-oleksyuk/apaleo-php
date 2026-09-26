<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Routing\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Support\Query;

final readonly class GetRoutingRequest extends Request
{
    /** @param list<'actions'> $expand */
    public function __construct(
        private string $routingId,
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/finance/v1/routings/'.rawurlencode($this->routingId);
    }

    public function query(): array
    {
        return array_filter([
            'expand' => Query::csv($this->expand),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
