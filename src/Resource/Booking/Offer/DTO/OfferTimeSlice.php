<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class OfferTimeSlice
{
    /** @param list<OfferService> $includedServices */
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public int $availableUnits,
        public Amount $baseAmount,
        public MonetaryValue $totalGrossAmount,
        public array $includedServices,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            availableUnits: ResponseData::int($data, 'availableUnits'),
            baseAmount: Amount::fromArray(ResponseData::nested($data, 'baseAmount')),
            totalGrossAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'totalGrossAmount')),
            includedServices: array_map(OfferService::fromArray(...), ResponseData::nestedList($data, 'includedServices')),
        );
    }
}
