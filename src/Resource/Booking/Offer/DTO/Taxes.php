<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Taxes
{
    public function __construct(
        public float $tax,
        public float $cityTax,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            tax: ResponseData::float($data, 'tax'),
            cityTax: ResponseData::float($data, 'cityTax'),
        );
    }
}
