<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Rate\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class ListRatesRequest extends Request
{
    public function __construct(
        private string $ratePlanId,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to,
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/rate-plans/'.rawurlencode($this->ratePlanId).'/rates';
    }

    public function query(): array
    {
        return array_filter([
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
