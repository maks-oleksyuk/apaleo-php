<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class OfferCityTax
{
    /** @param list<OfferCityTaxItem> $dates */
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public MonetaryValue $totalGrossAmount,
        public array $dates,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            totalGrossAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'totalGrossAmount')),
            dates: array_map(OfferCityTaxItem::fromArray(...), ResponseData::nestedList($data, 'dates')),
        );
    }
}
