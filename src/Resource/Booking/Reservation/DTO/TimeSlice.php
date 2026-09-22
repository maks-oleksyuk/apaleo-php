<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Action;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedRatePlan;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedUnit;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedUnitGroup;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class TimeSlice
{
    /**
     * @param list<ReservationService> $includedServices
     * @param list<Action>             $actions
     */
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public \DateTimeImmutable $serviceDate,
        public EmbeddedRatePlan $ratePlan,
        public EmbeddedUnitGroup $unitGroup,
        public ?EmbeddedUnit $unit,
        public Amount $baseAmount,
        public MonetaryValue $totalGrossAmount,
        public array $includedServices,
        public array $actions,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $unit = ResponseData::nested($data, 'unit');

        return new self(
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            serviceDate: ResponseData::date($data, 'serviceDate'),
            ratePlan: EmbeddedRatePlan::fromArray(ResponseData::nested($data, 'ratePlan')),
            unitGroup: EmbeddedUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
            unit: $unit !== [] ? EmbeddedUnit::fromArray($unit) : null,
            baseAmount: Amount::fromArray(ResponseData::nested($data, 'baseAmount')),
            totalGrossAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'totalGrossAmount')),
            includedServices: array_map(ReservationService::fromArray(...), ResponseData::nestedList($data, 'includedServices')),
            actions: array_map(Action::fromArray(...), ResponseData::nestedList($data, 'actions')),
        );
    }
}
