<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Group\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Group\GroupFilter;

final readonly class CountGroupsRequest extends Request
{
    public function __construct(
        private GroupFilter $filter,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/groups/$count';
    }

    public function query(): array
    {
        return $this->filter->toQuery();
    }
}
