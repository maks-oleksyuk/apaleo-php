<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\DayOfWeek;

/**
 * JSON Patch applied to every rate in [$from, $to), e.g. `(new JsonPatch())->replace('/price/amount', 120)`.
 * Only /price and /restrictions can be patched; derived rate plans only accept restrictions.
 */
final readonly class RatePatch
{
    /** @param list<DayOfWeek> $weekDays empty = every day */
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public JsonPatch $operations,
        public array $weekDays = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'weekDays' => array_map(static fn (DayOfWeek $d): string => $d->value, $this->weekDays) ?: null,
            'operations' => $this->operations->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
