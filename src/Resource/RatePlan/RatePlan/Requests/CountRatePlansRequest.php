<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\RatePlanFilter;

final readonly class CountRatePlansRequest extends Request
{
    public function __construct(
        private RatePlanFilter $filter = new RatePlanFilter(),
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/rate-plans/$count';
    }

    public function query(): array
    {
        return $this->filter->toQuery();
    }
}
