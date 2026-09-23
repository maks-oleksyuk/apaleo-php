<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Rate\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class DeleteRatesRequest extends Request
{
    public function __construct(
        private string $ratePlanId,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to,
    ) {}

    public function method(): Method
    {
        return Method::DELETE;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/rate-plans/'.rawurlencode($this->ratePlanId).'/rates';
    }

    public function query(): array
    {
        return ['from' => $this->from->format('Y-m-d'), 'to' => $this->to->format('Y-m-d')];
    }
}
