<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\ReservationValidationMessage;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationStatus;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\TravelPurpose;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The full snapshot carried by a "ReservationAdded" change entry (event type Created). */
final readonly class ReservationAddedChange
{
    /**
     * @param list<int>                 $childrenAges
     * @param list<PersonChange>        $additionalGuests
     * @param list<TimeSliceChange>     $timeSlices
     * @param list<ServiceChange>       $extraServices
     * @param list<ReservationValidationMessage> $validationMessages
     */
    public function __construct(
        public \DateTimeImmutable $arrival,
        public \DateTimeImmutable $departure,
        public ReservationStatus $status,
        public ?string $blockId,
        public MonetaryValue $totalGrossAmount,
        public int $adults,
        public ?string $comment,
        public ?string $guestComment,
        public ?string $externalCode,
        public ChannelCode $channelCode,
        public ?string $source,
        public ?PersonChange $primaryGuest,
        public array $childrenAges,
        public array $additionalGuests,
        public ?PaymentAccountChange $paymentAccount,
        public array $timeSlices,
        public array $extraServices,
        public GuaranteeType $guaranteeType,
        public CancellationFeeChange $cancellationFee,
        public MonetaryValue $noShowFee,
        public ?TravelPurpose $travelPurpose,
        public ?MonetaryValue $prePaymentAmount,
        public array $validationMessages,
        public ?string $companyId,
        public bool $hasCityTax,
        public ?string $marketSegmentId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $primaryGuest = ResponseData::nested($data, 'primaryGuest');
        $paymentAccount = ResponseData::nested($data, 'paymentAccount');
        $travelPurpose = ResponseData::nullableString($data, 'travelPurpose');
        $prePaymentAmount = ResponseData::nested($data, 'prePaymentAmount');

        return new self(
            arrival: ResponseData::dateTime($data, 'arrival'),
            departure: ResponseData::dateTime($data, 'departure'),
            status: ReservationStatus::fromApi(ResponseData::string($data, 'status')),
            blockId: ResponseData::nullableString($data, 'blockId'),
            totalGrossAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'totalGrossAmount')),
            adults: ResponseData::int($data, 'adults'),
            comment: ResponseData::nullableString($data, 'comment'),
            guestComment: ResponseData::nullableString($data, 'guestComment'),
            externalCode: ResponseData::nullableString($data, 'externalCode'),
            channelCode: ChannelCode::fromApi(ResponseData::string($data, 'channelCode')),
            source: ResponseData::nullableString($data, 'source'),
            primaryGuest: [] !== $primaryGuest ? PersonChange::fromArray($primaryGuest) : null,
            childrenAges: ResponseData::intList($data, 'childrenAges'),
            additionalGuests: array_map(PersonChange::fromArray(...), ResponseData::nestedList($data, 'additionalGuests')),
            paymentAccount: [] !== $paymentAccount ? PaymentAccountChange::fromArray($paymentAccount) : null,
            timeSlices: array_map(TimeSliceChange::fromArray(...), ResponseData::nestedList($data, 'timeSlices')),
            extraServices: array_map(ServiceChange::fromArray(...), ResponseData::nestedList($data, 'extraServices')),
            guaranteeType: GuaranteeType::fromApi(ResponseData::string($data, 'guaranteeType')),
            cancellationFee: CancellationFeeChange::fromArray(ResponseData::nested($data, 'cancellationFee')),
            noShowFee: MonetaryValue::fromArray(ResponseData::nested($data, 'noShowFee')),
            travelPurpose: null !== $travelPurpose ? TravelPurpose::from($travelPurpose) : null,
            prePaymentAmount: [] !== $prePaymentAmount ? MonetaryValue::fromArray($prePaymentAmount) : null,
            validationMessages: array_map(ReservationValidationMessage::fromArray(...), ResponseData::nestedList($data, 'validationMessages')),
            companyId: ResponseData::nullableString($data, 'companyId'),
            hasCityTax: ResponseData::bool($data, 'hasCityTax'),
            marketSegmentId: ResponseData::nullableString($data, 'marketSegmentId'),
        );
    }
}
