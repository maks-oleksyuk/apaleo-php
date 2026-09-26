<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Support\Query;

final readonly class GetRatePlanRequest extends Request
{
    /**
     * @param list<'cancellationPolicy'|'property'> $expand
     * @param ?list<string> $languages
     */
    public function __construct(
        private string $ratePlanId,
        private array $expand = [],
        private ?array $languages = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/rate-plans/'.rawurlencode($this->ratePlanId);
    }

    public function query(): array
    {
        return array_filter([
            'languages' => $this->languages !== null ? implode(',', $this->languages) : null,
            'expand' => Query::csv($this->expand),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
