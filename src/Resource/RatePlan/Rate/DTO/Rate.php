<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

/** One time slice (usually a night) of a rate plan; $price is null when no rate is set yet. */
final readonly class Rate
{
    /** @param list<CalculatedRate> $calculatedPrices */
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public ?MonetaryValue $price,
        public ?MonetaryValue $includedServicesPrice,
        public array $calculatedPrices,
        public ?RateRestrictions $restrictions,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            price: ResponseData::nullableNested($data, 'price', MonetaryValue::fromArray(...)),
            includedServicesPrice: ResponseData::nullableNested($data, 'includedServicesPrice', MonetaryValue::fromArray(...)),
            calculatedPrices: array_map(CalculatedRate::fromArray(...), ResponseData::nestedList($data, 'calculatedPrices')),
            restrictions: ResponseData::nullableNested($data, 'restrictions', RateRestrictions::fromArray(...)),
        );
    }
}
