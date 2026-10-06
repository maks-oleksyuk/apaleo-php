<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PropertyValue
{
    public function __construct(
        public EmbeddedProperty $property,
        public MonetaryValue $totalGrossAmount,
        public MonetaryValue $balance,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            property: ResponseData::requiredNested($data, 'property', EmbeddedProperty::fromArray(...)),
            totalGrossAmount: ResponseData::requiredNested($data, 'totalGrossAmount', MonetaryValue::fromArray(...)),
            balance: ResponseData::requiredNested($data, 'balance', MonetaryValue::fromArray(...)),
        );
    }
}
