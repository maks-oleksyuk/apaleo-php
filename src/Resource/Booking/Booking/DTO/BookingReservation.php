<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\ReservationCancellationFee;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\ReservationNoShowFee;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\ReservationServiceItem;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationStatus;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedCompany;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedRatePlan;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedUnitGroup;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\ExternalReferences;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\RegisteredCard;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The reservation summary embedded in a {@see Booking}; see ReservationResource::get() for the full reservation. */
final readonly class BookingReservation
{
    /**
     * @param list<int>                    $childrenAges
     * @param list<ReservationServiceItem> $services
     */
    public function __construct(
        public string $id,
        public ReservationStatus $status,
        public ?string $externalCode,
        public ChannelCode $channelCode,
        public ?string $source,
        public bool $hasActivePaymentAccount,
        public ?RegisteredCard $registeredCard,
        public \DateTimeImmutable $arrival,
        public \DateTimeImmutable $departure,
        public int $adults,
        public array $childrenAges,
        public MonetaryValue $totalGrossAmount,
        public EmbeddedProperty $property,
        public EmbeddedRatePlan $ratePlan,
        public EmbeddedUnitGroup $unitGroup,
        public array $services,
        public ?string $guestComment,
        public ReservationCancellationFee $cancellationFee,
        public ReservationNoShowFee $noShowFee,
        public ?EmbeddedCompany $company,
        public bool $isPreCheckedIn,
        public bool $isOpenForCharges,
        public ?ExternalReferences $externalReferences,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $registeredCard = ResponseData::nested($data, 'registeredCard');
        $company = ResponseData::nested($data, 'company');
        $externalReferences = ResponseData::nested($data, 'externalReferences');

        return new self(
            id: ResponseData::string($data, 'id'),
            status: ReservationStatus::fromApi(ResponseData::string($data, 'status')),
            externalCode: ResponseData::nullableString($data, 'externalCode'),
            channelCode: ChannelCode::fromApi(ResponseData::string($data, 'channelCode')),
            source: ResponseData::nullableString($data, 'source'),
            hasActivePaymentAccount: ResponseData::bool($data, 'hasActivePaymentAccount'),
            registeredCard: $registeredCard !== [] ? RegisteredCard::fromArray($registeredCard) : null,
            arrival: ResponseData::dateTime($data, 'arrival'),
            departure: ResponseData::dateTime($data, 'departure'),
            adults: ResponseData::int($data, 'adults'),
            childrenAges: ResponseData::intList($data, 'childrenAges'),
            totalGrossAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'totalGrossAmount')),
            property: EmbeddedProperty::fromArray(ResponseData::nested($data, 'property')),
            ratePlan: EmbeddedRatePlan::fromArray(ResponseData::nested($data, 'ratePlan')),
            unitGroup: EmbeddedUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
            services: array_map(ReservationServiceItem::fromArray(...), ResponseData::nestedList($data, 'services')),
            guestComment: ResponseData::nullableString($data, 'guestComment'),
            cancellationFee: ReservationCancellationFee::fromArray(ResponseData::nested($data, 'cancellationFee')),
            noShowFee: ReservationNoShowFee::fromArray(ResponseData::nested($data, 'noShowFee')),
            company: $company !== [] ? EmbeddedCompany::fromArray($company) : null,
            isPreCheckedIn: ResponseData::bool($data, 'isPreCheckedIn'),
            isOpenForCharges: ResponseData::bool($data, 'isOpenForCharges'),
            externalReferences: $externalReferences !== [] ? ExternalReferences::fromArray($externalReferences) : null,
        );
    }
}
