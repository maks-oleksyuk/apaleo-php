<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class DeleteReservationServiceRequest extends Request
{
    public function __construct(
        private string $reservationId,
        private string $serviceId,
    ) {}

    public function method(): Method
    {
        return Method::DELETE;
    }

    public function endpoint(): string
    {
        return '/booking/v1/reservations/'.rawurlencode($this->reservationId).'/services';
    }

    public function query(): array
    {
        return ['serviceId' => $this->serviceId];
    }
}
