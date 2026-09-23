<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ArchiveRatePlanRequest extends Request
{
    public function __construct(
        private string $ratePlanId,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/rate-plan-actions/'.rawurlencode($this->ratePlanId).'/archive';
    }
}
