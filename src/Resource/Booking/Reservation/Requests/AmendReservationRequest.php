<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\DesiredStayDetails;

final readonly class AmendReservationRequest extends Request
{
    public function __construct(
        private string $reservationId,
        private DesiredStayDetails $details,
        private bool $force = false,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        $endpoint = '/booking/v1/reservation-actions/'.rawurlencode($this->reservationId).'/amend';

        return $this->force ? $endpoint.'/$force' : $endpoint;
    }

    public function body(): array
    {
        return $this->details->toArray();
    }
}
