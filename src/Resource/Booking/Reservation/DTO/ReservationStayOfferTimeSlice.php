<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\OfferUnitGroup;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedRatePlan;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ReservationStayOfferTimeSlice
{
    /** @param list<ReservationStayOfferService> $includedServices */
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public EmbeddedRatePlan $ratePlan,
        public OfferUnitGroup $unitGroup,
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
            ratePlan: EmbeddedRatePlan::fromArray(ResponseData::nested($data, 'ratePlan')),
            unitGroup: OfferUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
            baseAmount: Amount::fromArray(ResponseData::nested($data, 'baseAmount')),
            totalGrossAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'totalGrossAmount')),
            includedServices: array_map(ReservationStayOfferService::fromArray(...), ResponseData::nestedList($data, 'includedServices')),
        );
    }
}
