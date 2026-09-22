<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\CreateReservation;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Booker;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\RegisteredCard;

/**
 * The payload for creating a booking. Does not expose the deprecated inline `paymentAccount`
 * field (superseded by the dedicated PaymentAccounts resource, being removed 2026-05-15).
 */
final readonly class CreateBooking
{
    /** @param list<CreateReservation> $reservations */
    public function __construct(
        public Booker $booker,
        public array $reservations,
        public ?RegisteredCard $registeredCard = null,
        public ?string $comment = null,
        public ?string $bookerComment = null,
        public ?string $transactionReference = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'booker' => $this->booker->toArray(),
            'reservations' => array_map(static fn (CreateReservation $r): array => $r->toArray(), $this->reservations),
            'registeredCard' => $this->registeredCard?->toArray(),
            'comment' => $this->comment,
            'bookerComment' => $this->bookerComment,
            'transactionReference' => $this->transactionReference,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
