<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class StayOffers
{
    /** @param list<Offer> $offers */
    public function __construct(
        public EmbeddedProperty $property,
        public array $offers,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            property: EmbeddedProperty::fromArray(ResponseData::nested($data, 'property')),
            offers: array_map(Offer::fromArray(...), ResponseData::nestedList($data, 'offers')),
        );
    }
}
