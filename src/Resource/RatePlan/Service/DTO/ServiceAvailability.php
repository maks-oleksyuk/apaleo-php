<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Service\Enum\AvailabilityMode;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\DayOfWeek;
use Oleksyuk\Apaleo\Support\ResponseData;

/** When during a stay the service can be booked, and how many per day. */
final readonly class ServiceAvailability
{
    /** @param list<DayOfWeek> $daysOfWeek */
    public function __construct(
        public AvailabilityMode $mode,
        public ?int $quantity = null,
        public array $daysOfWeek = [],
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            mode: AvailabilityMode::fromApi(ResponseData::string($data, 'mode')),
            quantity: ResponseData::nullableInt($data, 'quantity'),
            daysOfWeek: array_map(DayOfWeek::fromApi(...), ResponseData::stringListOrEmpty($data, 'daysOfWeek')),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'mode' => $this->mode->value,
            'quantity' => $this->quantity,
            'daysOfWeek' => array_map(static fn (DayOfWeek $d): string => $d->value, $this->daysOfWeek) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
