<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Booking\DTO\CreateBooking;

final class CreateBookingRequest extends Request
{
    /**
     * @param ?string $idempotencyKey lets a retried request be recognized and not double-book; strongly recommended
     */
    public function __construct(
        private readonly CreateBooking $booking,
        private readonly bool $force = false,
        private readonly ?string $idempotencyKey = null,
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
