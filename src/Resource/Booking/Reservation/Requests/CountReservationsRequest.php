<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\ReservationFilter;

final class CountReservationsRequest extends Request
{
    public function __construct(
        private readonly ReservationFilter $filter,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/reservations/$count';
    }

    public function query(): array
    {
        return $this->filter->toQuery();
    }
}
