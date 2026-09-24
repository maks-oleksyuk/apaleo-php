<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Booking\DTO\CreateBooking;

final readonly class CreateBookingRequest extends Request
{
    public function __construct(
        private CreateBooking $booking,
        private bool $force = false,
        private ?string $idempotencyKey = null,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return $this->force ? '/booking/v1/bookings/$force' : '/booking/v1/bookings';
    }

    public function body(): array
    {
        return $this->booking->toArray();
    }

    public function headers(): array
    {
        return $this->idempotencyKey !== null ? ['Idempotency-Key' => $this->idempotencyKey] : [];
    }
}
