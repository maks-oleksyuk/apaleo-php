<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;

final readonly class GetReservationServiceOffersRequest extends Request
{
    public function __construct(
        private string $reservationId,
        private ?ChannelCode $channelCode = null,
        private ?bool $onlyDefaultDates = null,
        private ?bool $includeUnavailable = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/reservations/'.rawurlencode($this->reservationId).'/service-offers';
    }

    public function query(): array
    {
        return array_filter([
            'channelCode' => $this->channelCode?->value,
            'onlyDefaultDates' => $this->onlyDefaultDates,
            'includeUnavailable' => $this->includeUnavailable,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
