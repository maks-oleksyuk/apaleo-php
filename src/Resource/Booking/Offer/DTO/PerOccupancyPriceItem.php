<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PerOccupancyPriceItem
{
    public function __construct(
        public int $adults,
        public Price $price,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            adults: ResponseData::int($data, 'adults'),
            price: Price::fromArray(ResponseData::nested($data, 'price')),
        );
    }
}
