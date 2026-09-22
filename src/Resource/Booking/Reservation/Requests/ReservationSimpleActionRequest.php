<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

/** Shared shape for the reservation-actions endpoints that take no query params or body: checkout, cancel, noshow, revert-checkin, add-city-tax, remove-city-tax, lock-unit, unlock-unit, unassign-units. */
final class ReservationSimpleActionRequest extends Request
{
    public function __construct(
        private readonly string $reservationId,
        private readonly string $action,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/booking/v1/reservation-actions/'.rawurlencode($this->reservationId).'/'.$this->action;
    }
}
