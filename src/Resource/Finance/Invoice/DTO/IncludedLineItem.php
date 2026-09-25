<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Support\ResponseData;

/** A service included in a line item's price, e.g. breakfast in the room rate. */
final readonly class IncludedLineItem
{
    public function __construct(
        public ?string $description,
        public MonetaryValue $price,
        public ?VatType $vatType,
        public ?float $vatPercent,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $vatType = ResponseData::nullableString($data, 'vatType');

        return new self(
            description: ResponseData::nullableString($data, 'description'),
            price: MonetaryValue::fromArray(ResponseData::nested($data, 'price')),
            vatType: $vatType !== null ? VatType::fromApi($vatType) : null,
            vatPercent: ResponseData::nullableFloat($data, 'vatPercent'),
        );
    }
}
