<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationStatus;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Action;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Booker;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Commission;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedCompany;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedMarketSegment;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedRatePlan;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedUnit;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedUnitGroup;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\ExternalReferences;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Guest;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\RegisteredCard;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\TravelPurpose;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Item shape of GET /booking/v1/reservations: unlike Reservation, it has no $payableAmount and $taxDetails. */
final readonly class ReservationListItem
{
    /**
     * @param list<int>                          $childrenAges
     * @param list<Guest>                         $additionalGuests
     * @param list<TimeSlice>                     $timeSlices
     * @param list<ReservationServiceItem>        $services
     * @param list<ReservationAssignedUnit>       $assignedUnits
     * @param list<ReservationValidationMessage>  $validationMessages
     * @param list<Action>                        $actions
     */
    public function __construct(
        public string $id,
        public string $bookingId,
        public ?string $blockId,
        public ?string $groupName,
        public ReservationStatus $status,
        public ?\DateTimeImmutable $checkInTime,
        public ?\DateTimeImmutable $checkOutTime,
        public ?\DateTimeImmutable $cancellationTime,
        public ?\DateTimeImmutable $noShowTime,
        public ?EmbeddedUnit $unit,
        public EmbeddedProperty $property,
        public EmbeddedRatePlan $ratePlan,
        public EmbeddedUnitGroup $unitGroup,
        public MonetaryValue $totalGrossAmount,
        public \DateTimeImmutable $arrival,
        public \DateTimeImmutable $departure,
        public \DateTimeImmutable $created,
        public \DateTimeImmutable $modified,
        public int $adults,
        public array $childrenAges,
        public ?string $comment,
        public ?string $guestComment,
        public ?string $externalCode,
        public ChannelCode $channelCode,
        public ?string $source,
        public ?Guest $primaryGuest,
        public array $additionalGuests,
        public ?Booker $booker,
        public bool $hasActivePaymentAccount,
        public ?RegisteredCard $registeredCard,
        public array $timeSlices,
        public array $services,
        public GuaranteeType $guaranteeType,
        public ?ReservationCancellationFee $cancellationFee,
        public ?ReservationNoShowFee $noShowFee,
        public ?TravelPurpose $travelPurpose,
        public MonetaryValue $balance,
        public array $assignedUnits,
        public array $validationMessages,
        public array $actions,
        public ?EmbeddedCompany $company,
        public ?string $corporateCode,
        public bool $allFoliosHaveInvoice,
        public bool $hasCityTax,
        public ?Commission $commission,
        public ?string $promoCode,
        public bool $isPreCheckedIn,
        public bool $isOpenForCharges,
        public ?EmbeddedMarketSegment $marketSegment,
        public ?ExternalReferences $externalReferences,
        public bool $isUnitAssignmentLocked,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $unit = ResponseData::nested($data, 'unit');
        $primaryGuest = ResponseData::nested($data, 'primaryGuest');
        $booker = ResponseData::nested($data, 'booker');
        $registeredCard = ResponseData::nested($data, 'registeredCard');
        $cancellationFee = ResponseData::nested($data, 'cancellationFee');
        $noShowFee = ResponseData::nested($data, 'noShowFee');
        $travelPurpose = ResponseData::nullableString($data, 'travelPurpose');
        $company = ResponseData::nested($data, 'company');
        $commission = ResponseData::nested($data, 'commission');
        $marketSegment = ResponseData::nested($data, 'marketSegment');
        $externalReferences = ResponseData::nested($data, 'externalReferences');

        return new self(
            id: ResponseData::string($data, 'id'),
            bookingId: ResponseData::string($data, 'bookingId'),
            blockId: ResponseData::nullableString($data, 'blockId'),
            groupName: ResponseData::nullableString($data, 'groupName'),
            status: ReservationStatus::fromApi(ResponseData::string($data, 'status')),
            checkInTime: ResponseData::nullableDateTime($data, 'checkInTime'),
            checkOutTime: ResponseData::nullableDateTime($data, 'checkOutTime'),
            cancellationTime: ResponseData::nullableDateTime($data, 'cancellationTime'),
            noShowTime: ResponseData::nullableDateTime($data, 'noShowTime'),
            unit: $unit !== [] ? EmbeddedUnit::fromArray($unit) : null,
            property: EmbeddedProperty::fromArray(ResponseData::nested($data, 'property')),
            ratePlan: EmbeddedRatePlan::fromArray(ResponseData::nested($data, 'ratePlan')),
            unitGroup: EmbeddedUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
            totalGrossAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'totalGrossAmount')),
            arrival: ResponseData::dateTime($data, 'arrival'),
            departure: ResponseData::dateTime($data, 'departure'),
            created: ResponseData::dateTime($data, 'created'),
            modified: ResponseData::dateTime($data, 'modified'),
            adults: ResponseData::int($data, 'adults'),
            childrenAges: ResponseData::intList($data, 'childrenAges'),
            comment: ResponseData::nullableString($data, 'comment'),
            guestComment: ResponseData::nullableString($data, 'guestComment'),
            externalCode: ResponseData::nullableString($data, 'externalCode'),
            channelCode: ChannelCode::fromApi(ResponseData::string($data, 'channelCode')),
            source: ResponseData::nullableString($data, 'source'),
            primaryGuest: $primaryGuest !== [] ? Guest::fromArray($primaryGuest) : null,
            additionalGuests: array_map(Guest::fromArray(...), ResponseData::nestedList($data, 'additionalGuests')),
            booker: $booker !== [] ? Booker::fromArray($booker) : null,
            hasActivePaymentAccount: ResponseData::bool($data, 'hasActivePaymentAccount'),
            registeredCard: $registeredCard !== [] ? RegisteredCard::fromArray($registeredCard) : null,
            timeSlices: array_map(TimeSlice::fromArray(...), ResponseData::nestedList($data, 'timeSlices')),
            services: array_map(ReservationServiceItem::fromArray(...), ResponseData::nestedList($data, 'services')),
            guaranteeType: GuaranteeType::fromApi(ResponseData::string($data, 'guaranteeType')),
            cancellationFee: $cancellationFee !== [] ? ReservationCancellationFee::fromArray($cancellationFee) : null,
            noShowFee: $noShowFee !== [] ? ReservationNoShowFee::fromArray($noShowFee) : null,
            travelPurpose: $travelPurpose !== null ? TravelPurpose::fromApi($travelPurpose) : null,
            balance: MonetaryValue::fromArray(ResponseData::nested($data, 'balance')),
            assignedUnits: array_map(ReservationAssignedUnit::fromArray(...), ResponseData::nestedList($data, 'assignedUnits')),
            validationMessages: array_map(ReservationValidationMessage::fromArray(...), ResponseData::nestedList($data, 'validationMessages')),
            actions: array_map(Action::fromArray(...), ResponseData::nestedList($data, 'actions')),
            company: $company !== [] ? EmbeddedCompany::fromArray($company) : null,
            corporateCode: ResponseData::nullableString($data, 'corporateCode'),
            allFoliosHaveInvoice: ResponseData::bool($data, 'allFoliosHaveInvoice'),
            hasCityTax: ResponseData::bool($data, 'hasCityTax'),
            commission: $commission !== [] ? Commission::fromArray($commission) : null,
            promoCode: ResponseData::nullableString($data, 'promoCode'),
            isPreCheckedIn: ResponseData::bool($data, 'isPreCheckedIn'),
            isOpenForCharges: ResponseData::bool($data, 'isOpenForCharges'),
            marketSegment: $marketSegment !== [] ? EmbeddedMarketSegment::fromArray($marketSegment) : null,
            externalReferences: $externalReferences !== [] ? ExternalReferences::fromArray($externalReferences) : null,
            isUnitAssignmentLocked: ResponseData::bool($data, 'isUnitAssignmentLocked'),
        );
    }
}
