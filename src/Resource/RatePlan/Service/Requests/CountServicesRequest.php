<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\RatePlan\Service\ServiceFilter;

final readonly class CountServicesRequest extends Request
{
    public function __construct(
        private ServiceFilter $filter = new ServiceFilter(),
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/services/$count';
    }

    public function query(): array
    {
        return $this->filter->toQuery();
    }
}
