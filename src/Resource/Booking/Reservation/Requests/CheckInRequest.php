<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class CheckInRequest extends Request
{
    public function __construct(
        private readonly string $reservationId,
        private readonly ?bool $withCityTax = null,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/booking/v1/reservation-actions/'.rawurlencode($this->reservationId).'/checkin';
    }

    public function query(): array
    {
        return array_filter(['withCityTax' => $this->withCityTax], static fn (mixed $value): bool => $value !== null);
    }
}
