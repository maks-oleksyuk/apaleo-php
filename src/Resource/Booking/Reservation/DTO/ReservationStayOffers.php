<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ReservationStayOffers
{
    /**
     * $property is null when there were no offers at all (apaleo returns 204 No Content for
     * "there are no available offers for the specified parameters" — a normal, common result).
     *
     * @param list<ReservationStayOffer> $offers
     */
    public function __construct(
        public ?EmbeddedProperty $property,
        public array $offers,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            property: ResponseData::nullableNested($data, 'property', EmbeddedProperty::fromArray(...)),
            offers: array_map(ReservationStayOffer::fromArray(...), ResponseData::nestedList($data, 'offers')),
        );
    }
}
