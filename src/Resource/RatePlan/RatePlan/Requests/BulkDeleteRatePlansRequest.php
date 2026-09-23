<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class BulkDeleteRatePlansRequest extends Request
{
    /** @param list<string> $ratePlanIds */
    public function __construct(
        private array $ratePlanIds,
    ) {}

    public function method(): Method
    {
        return Method::DELETE;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/rate-plans';
    }

    public function query(): array
    {
        return ['ratePlanIds' => implode(',', $this->ratePlanIds)];
    }
}
