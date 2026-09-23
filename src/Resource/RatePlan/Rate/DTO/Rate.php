<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\MonetaryValue;
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
        $price = ResponseData::nested($data, 'price');
        $includedServicesPrice = ResponseData::nested($data, 'includedServicesPrice');
        $restrictions = ResponseData::nested($data, 'restrictions');

        return new self(
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            price: $price !== [] ? MonetaryValue::fromArray($price) : null,
            includedServicesPrice: $includedServicesPrice !== [] ? MonetaryValue::fromArray($includedServicesPrice) : null,
            calculatedPrices: array_map(CalculatedRate::fromArray(...), ResponseData::nestedList($data, 'calculatedPrices')),
            restrictions: $restrictions !== [] ? RateRestrictions::fromArray($restrictions) : null,
        );
    }
}
