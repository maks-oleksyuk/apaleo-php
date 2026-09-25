<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationStatus;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Action;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Booker;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Commission;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedCompany;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedRatePlan;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedUnit;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\ExternalReferences;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Guest;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\PayableAmount;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\RegisteredCard;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\TaxDetail;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedMarketSegment;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedUnitGroup;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TravelPurpose;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Reservation
{
    /**
     * @param list<int>                          $childrenAges
     * @param list<Guest>                         $additionalGuests
     * @param list<TimeSlice>                     $timeSlices
     * @param list<ReservationServiceItem>        $services
     * @param list<ReservationAssignedUnit>       $assignedUnits
     * @param list<ReservationValidationMessage>  $validationMessages
     * @param list<Action>                        $actions
     * @param list<TaxDetail>                     $taxDetails
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
        public array $taxDetails,
        public bool $hasCityTax,
        public ?Commission $commission,
        public ?string $promoCode,
        public PayableAmount $payableAmount,
        public bool $isPreCheckedIn,
        public bool $isOpenForCharges,
        public ?EmbeddedMarketSegment $marketSegment,
        public ?ExternalReferences $externalReferences,
        public bool $isUnitAssignmentLocked,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $travelPurpose = ResponseData::nullableString($data, 'travelPurpose');

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
            unit: ResponseData::nullableNested($data, 'unit', EmbeddedUnit::fromArray(...)),
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
            primaryGuest: ResponseData::nullableNested($data, 'primaryGuest', Guest::fromArray(...)),
            additionalGuests: array_map(Guest::fromArray(...), ResponseData::nestedList($data, 'additionalGuests')),
            booker: ResponseData::nullableNested($data, 'booker', Booker::fromArray(...)),
            hasActivePaymentAccount: ResponseData::bool($data, 'hasActivePaymentAccount'),
            registeredCard: ResponseData::nullableNested($data, 'registeredCard', RegisteredCard::fromArray(...)),
            timeSlices: array_map(TimeSlice::fromArray(...), ResponseData::nestedList($data, 'timeSlices')),
            services: array_map(ReservationServiceItem::fromArray(...), ResponseData::nestedList($data, 'services')),
            guaranteeType: GuaranteeType::fromApi(ResponseData::string($data, 'guaranteeType')),
            cancellationFee: ResponseData::nullableNested($data, 'cancellationFee', ReservationCancellationFee::fromArray(...)),
            noShowFee: ResponseData::nullableNested($data, 'noShowFee', ReservationNoShowFee::fromArray(...)),
            travelPurpose: $travelPurpose !== null ? TravelPurpose::fromApi($travelPurpose) : null,
            balance: MonetaryValue::fromArray(ResponseData::nested($data, 'balance')),
            assignedUnits: array_map(ReservationAssignedUnit::fromArray(...), ResponseData::nestedList($data, 'assignedUnits')),
            validationMessages: array_map(ReservationValidationMessage::fromArray(...), ResponseData::nestedList($data, 'validationMessages')),
            actions: array_map(Action::fromArray(...), ResponseData::nestedList($data, 'actions')),
            company: ResponseData::nullableNested($data, 'company', EmbeddedCompany::fromArray(...)),
            corporateCode: ResponseData::nullableString($data, 'corporateCode'),
            allFoliosHaveInvoice: ResponseData::bool($data, 'allFoliosHaveInvoice'),
            taxDetails: array_map(TaxDetail::fromArray(...), ResponseData::nestedList($data, 'taxDetails')),
            hasCityTax: ResponseData::bool($data, 'hasCityTax'),
            commission: ResponseData::nullableNested($data, 'commission', Commission::fromArray(...)),
            promoCode: ResponseData::nullableString($data, 'promoCode'),
            payableAmount: PayableAmount::fromArray(ResponseData::nested($data, 'payableAmount')),
            isPreCheckedIn: ResponseData::bool($data, 'isPreCheckedIn'),
            isOpenForCharges: ResponseData::bool($data, 'isOpenForCharges'),
            marketSegment: ResponseData::nullableNested($data, 'marketSegment', EmbeddedMarketSegment::fromArray(...)),
            externalReferences: ResponseData::nullableNested($data, 'externalReferences', ExternalReferences::fromArray(...)),
            isUnitAssignmentLocked: ResponseData::bool($data, 'isUnitAssignmentLocked'),
        );
    }
}
