<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;

final readonly class DesiredTimeSlice
{
    public function __construct(
        public string $ratePlanId,
        public ?MonetaryValue $totalGrossAmount = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'ratePlanId' => $this->ratePlanId,
            'totalGrossAmount' => $this->totalGrossAmount?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
