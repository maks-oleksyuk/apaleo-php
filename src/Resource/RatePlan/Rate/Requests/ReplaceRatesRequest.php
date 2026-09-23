<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Rate\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO\ReplaceRate;

final readonly class ReplaceRatesRequest extends Request
{
    /** @param list<ReplaceRate> $rates */
    public function __construct(
        private string $ratePlanId,
        private array $rates,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/rate-plans/'.rawurlencode($this->ratePlanId).'/rates';
    }

    public function body(): array
    {
        return ['rates' => array_map(static fn (ReplaceRate $r): array => $r->toArray(), $this->rates)];
    }
}
