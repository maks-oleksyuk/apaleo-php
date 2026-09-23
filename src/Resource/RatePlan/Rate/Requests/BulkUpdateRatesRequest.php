<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Rate\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\DayOfWeek;

final readonly class BulkUpdateRatesRequest extends Request
{
    /**
     * @param list<string> $ratePlanIds
     * @param list<DayOfWeek> $weekDays empty = every day
     */
    public function __construct(
        private array $ratePlanIds,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to,
        private JsonPatch $patch,
        private array $weekDays = [],
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/rateplan/v1/rates';
    }

    public function query(): array
    {
        return array_filter([
            'ratePlanIds' => implode(',', $this->ratePlanIds),
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'weekDays' => implode(',', array_map(static fn (DayOfWeek $d): string => $d->value, $this->weekDays)) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
