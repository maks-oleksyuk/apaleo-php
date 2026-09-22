<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\CreateReservation;

final readonly class AddReservationsRequest extends Request
{
    /** @param list<CreateReservation> $reservations */
    public function __construct(
        private string $bookingId,
        private array $reservations,
        private bool $force = false,
        private ?string $transactionReference = null,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        $endpoint = '/booking/v1/bookings/'.rawurlencode($this->bookingId).'/reservations';

        return $this->force ? $endpoint.'/$force' : $endpoint;
    }

    public function body(): array
    {
        return array_filter([
            'reservations' => array_map(static fn (CreateReservation $r): array => $r->toArray(), $this->reservations),
            'transactionReference' => $this->transactionReference,
        ], static fn (mixed $value): bool => $value !== null);
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }
}
