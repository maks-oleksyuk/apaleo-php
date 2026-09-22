<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;

final readonly class ServiceBookingDate
{
    public function __construct(
        public string $serviceDate,
        public ?int $count = null,
        public ?MonetaryValue $amount = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'serviceDate' => $this->serviceDate,
            'count' => $this->count,
            'amount' => $this->amount?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
