<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The price for a given occupancy, surcharges applied. */
final readonly class CalculatedRate
{
    public function __construct(
        public int $adults,
        public MonetaryValue $price,
        public ?MonetaryValue $includedServicesPrice,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            adults: ResponseData::int($data, 'adults'),
            price: MonetaryValue::fromArray(ResponseData::nested($data, 'price')),
            includedServicesPrice: ResponseData::nullableNested($data, 'includedServicesPrice', MonetaryValue::fromArray(...)),
        );
    }
}
