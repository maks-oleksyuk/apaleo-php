<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO\ReplaceRatePlan;

final readonly class ReplaceRatePlanRequest extends Request
{
    public function __construct(
        private string $ratePlanId,
        private ReplaceRatePlan $data,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/rate-plans/'.rawurlencode($this->ratePlanId);
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
