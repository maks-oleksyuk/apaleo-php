<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;

final class GetReservationOffersRequest extends Request
{
    /**
     * @param list<int>    $childrenAges
     * @param list<string> $unitGroupIds
     */
    public function __construct(
        private readonly string $reservationId,
        private readonly ?string $arrival = null,
        private readonly ?string $departure = null,
        private readonly ?int $adults = null,
        private readonly array $childrenAges = [],
        private readonly ?ChannelCode $channelCode = null,
        private readonly ?string $promoCode = null,
        private readonly ?string $corporateCode = null,
        private readonly ?bool $requote = null,
        private readonly ?bool $includeUnavailable = null,
        private readonly array $unitGroupIds = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/reservations/'.rawurlencode($this->reservationId).'/offers';
    }

    public function query(): array
    {
        return array_filter([
            'arrival' => $this->arrival,
            'departure' => $this->departure,
            'adults' => $this->adults,
            'childrenAges' => implode(',', $this->childrenAges) ?: null,
            'channelCode' => $this->channelCode?->value,
            'promoCode' => $this->promoCode,
            'corporateCode' => $this->corporateCode,
            'requote' => $this->requote,
            'includeUnavailable' => $this->includeUnavailable,
            'unitGroupIds' => implode(',', $this->unitGroupIds) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
