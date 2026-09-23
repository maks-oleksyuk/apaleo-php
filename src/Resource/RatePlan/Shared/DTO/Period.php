<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Period
{
    public function __construct(
        public ?int $hours = null,
        public ?int $days = null,
        public ?int $months = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            hours: ResponseData::nullableInt($data, 'hours'),
            days: ResponseData::nullableInt($data, 'days'),
            months: ResponseData::nullableInt($data, 'months'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'hours' => $this->hours,
            'days' => $this->days,
            'months' => $this->months,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
