<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class TaxDetail
{
    public function __construct(
        public VatType $vatType,
        public float $vatPercent,
        public MonetaryValue $net,
        public MonetaryValue $tax,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            vatType: VatType::fromApi(ResponseData::string($data, 'vatType')),
            vatPercent: ResponseData::float($data, 'vatPercent'),
            net: MonetaryValue::fromArray(ResponseData::nested($data, 'net')),
            tax: MonetaryValue::fromArray(ResponseData::nested($data, 'tax')),
        );
    }
}
