<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO\CreateRatePlan;

final readonly class CreateRatePlanRequest extends Request
{
    public function __construct(
        private CreateRatePlan $data,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/rate-plans';
    }

    public function body(): array
    {
        return $this->data->toArray();
    }
}
