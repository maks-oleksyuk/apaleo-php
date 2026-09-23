<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Rate\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO\RatePatch;

final readonly class UpdateRatesRequest extends Request
{
    /** @param list<RatePatch> $patches */
    public function __construct(
        private string $ratePlanId,
        private array $patches,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/rate-plans/'.rawurlencode($this->ratePlanId).'/rates';
    }

    public function body(): array
    {
        return ['rates' => array_map(static fn (RatePatch $p): array => $p->toArray(), $this->patches)];
    }
}
