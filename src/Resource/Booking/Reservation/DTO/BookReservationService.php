<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;

final readonly class BookReservationService
{
    /** @param list<ServiceBookingDate> $dates */
    public function __construct(
        public string $serviceId,
        public ?int $count = null,
        public ?MonetaryValue $amount = null,
        public array $dates = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'serviceId' => $this->serviceId,
            'count' => $this->count,
            'amount' => $this->amount?->toArray(),
            'dates' => $this->dates !== [] ? array_map(static fn (ServiceBookingDate $d): array => $d->toArray(), $this->dates) : null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
