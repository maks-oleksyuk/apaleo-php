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
            ratePlan: ResponseData::requiredNested($data, 'ratePlan', EmbeddedRatePlan::fromArray(...)),
            unitGroup: ResponseData::requiredNested($data, 'unitGroup', OfferUnitGroup::fromArray(...)),
            baseAmount: ResponseData::requiredNested($data, 'baseAmount', Amount::fromArray(...)),
            totalGrossAmount: ResponseData::requiredNested($data, 'totalGrossAmount', MonetaryValue::fromArray(...)),
            includedServices: ResponseData::mapList($data, 'includedServices', ReservationStayOfferService::fromArray(...)),
        );
    }
}
