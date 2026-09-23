<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class BulkUpdateRatePlansRequest extends Request
{
    /** @param list<string> $ratePlanIds */
    public function __construct(
        private array $ratePlanIds,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/rate-plans';
    }

    public function query(): array
    {
        return ['ratePlanIds' => implode(',', $this->ratePlanIds)];
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
