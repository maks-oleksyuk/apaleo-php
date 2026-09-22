<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ReservationStayOffers
{
    /** @param list<ReservationStayOffer> $offers */
    public function __construct(
        public EmbeddedProperty $property,
        public array $offers,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            property: EmbeddedProperty::fromArray(ResponseData::nested($data, 'property')),
            offers: array_map(ReservationStayOffer::fromArray(...), ResponseData::nestedList($data, 'offers')),
        );
    }
}
