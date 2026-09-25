<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TravelPurpose;
use Oleksyuk\Apaleo\Support\ResponseData;

/** A partial diff carried by a "ReservationChanged" change entry — every field is optional. */
final readonly class ReservationChangedChange
{
    /** @param list<int> $childrenAges */
    public function __construct(
        public ?bool $isPreCheckedIn,
        public ?\DateTimeImmutable $arrival,
        public ?\DateTimeImmutable $departure,
        public ?int $adults,
        public array $childrenAges,
        public ?string $comment,
        public ?string $guestComment,
        public ?MonetaryValue $totalGrossAmount,
        public ?PersonChange $primaryGuest,
        public ?PaymentAccountChange $paymentAccount,
        public ?GuaranteeType $guaranteeType,
        public ?TravelPurpose $travelPurpose,
        public ?string $companyId,
        public ?MonetaryValue $noShowFee,
        public ?CancellationFeeChange $cancellationFee,
        public ?MonetaryValue $commissionAmount,
        public ?bool $hasCityTax,
        public ?string $marketSegmentId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $totalGrossAmount = ResponseData::nested($data, 'totalGrossAmount');
        $primaryGuest = ResponseData::nested($data, 'primaryGuest');
        $paymentAccount = ResponseData::nested($data, 'paymentAccount');
        $guaranteeType = ResponseData::nullableString($data, 'guaranteeType');
        $travelPurpose = ResponseData::nullableString($data, 'travelPurpose');
        $noShowFee = ResponseData::nested($data, 'noShowFee');
        $cancellationFee = ResponseData::nested($data, 'cancellationFee');
        $commissionAmount = ResponseData::nested($data, 'commissionAmount');

        return new self(
            isPreCheckedIn: \array_key_exists('isPreCheckedIn', $data) ? ResponseData::bool($data, 'isPreCheckedIn') : null,
            arrival: ResponseData::nullableDateTime($data, 'arrival'),
            departure: ResponseData::nullableDateTime($data, 'departure'),
            adults: ResponseData::nullableInt($data, 'adults'),
            childrenAges: ResponseData::intList($data, 'childrenAges'),
            comment: ResponseData::nullableString($data, 'comment'),
            guestComment: ResponseData::nullableString($data, 'guestComment'),
            totalGrossAmount: [] !== $totalGrossAmount ? MonetaryValue::fromArray($totalGrossAmount) : null,
            primaryGuest: [] !== $primaryGuest ? PersonChange::fromArray($primaryGuest) : null,
            paymentAccount: [] !== $paymentAccount ? PaymentAccountChange::fromArray($paymentAccount) : null,
            guaranteeType: null !== $guaranteeType ? GuaranteeType::fromApi($guaranteeType) : null,
            travelPurpose: null !== $travelPurpose ? TravelPurpose::fromApi($travelPurpose) : null,
            companyId: ResponseData::nullableString($data, 'companyId'),
            noShowFee: [] !== $noShowFee ? MonetaryValue::fromArray($noShowFee) : null,
            cancellationFee: [] !== $cancellationFee ? CancellationFeeChange::fromArray($cancellationFee) : null,
            commissionAmount: [] !== $commissionAmount ? MonetaryValue::fromArray($commissionAmount) : null,
            hasCityTax: \array_key_exists('hasCityTax', $data) ? ResponseData::bool($data, 'hasCityTax') : null,
            marketSegmentId: ResponseData::nullableString($data, 'marketSegmentId'),
        );
    }
}
