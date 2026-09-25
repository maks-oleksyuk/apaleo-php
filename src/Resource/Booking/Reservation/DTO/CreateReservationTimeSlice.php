<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;

final readonly class CreateReservationTimeSlice
{
    public function __construct(
        public string $ratePlanId,
        public ?MonetaryValue $totalAmount = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'ratePlanId' => $this->ratePlanId,
            'totalAmount' => $this->totalAmount?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
