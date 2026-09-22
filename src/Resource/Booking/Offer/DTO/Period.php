<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Period
{
    public function __construct(
        public ?int $hours,
        public ?int $days,
        public ?int $months,
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
}
