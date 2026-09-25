<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedService;
use Oleksyuk\Apaleo\Support\ResponseData;

/** A service included in a rate plan (package element) — see {@see ReservationServiceItem} for extras booked separately. */
final readonly class ReservationService
{
    public function __construct(
        public EmbeddedService $service,
        public \DateTimeImmutable $serviceDate,
        public int $count,
        public Amount $amount,
        public bool $bookedAsExtra,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            service: EmbeddedService::fromArray(ResponseData::nested($data, 'service')),
            serviceDate: ResponseData::date($data, 'serviceDate'),
            count: ResponseData::int($data, 'count'),
            amount: Amount::fromArray(ResponseData::nested($data, 'amount')),
            bookedAsExtra: ResponseData::bool($data, 'bookedAsExtra'),
        );
    }
}
