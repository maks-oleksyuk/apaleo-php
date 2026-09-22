<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Block\BlockFilter;

final class CountBlocksRequest extends Request
{
    public function __construct(
        private readonly BlockFilter $filter,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/blocks/$count';
    }

    public function query(): array
    {
        return $this->filter->toQuery();
    }
}
