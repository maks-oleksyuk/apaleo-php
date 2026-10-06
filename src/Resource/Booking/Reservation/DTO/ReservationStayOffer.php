<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\OfferCancellationFee;
use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\OfferCityTax;
use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\OfferFee;
use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\OfferNoShowFee;
use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\OfferValidationMessage;
use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\ServiceOffer;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\TaxDetail;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

/** An offer for amending an existing reservation's stay; see OfferResource for offers on a brand-new stay. */
final readonly class ReservationStayOffer
{
    /**
     * @param list<ReservationStayOfferTimeSlice> $timeSlices
     * @param list<ServiceOffer>                  $services
     * @param list<OfferFee>                      $fees
     * @param list<TaxDetail>                     $taxDetails
     * @param list<OfferValidationMessage>         $validationMessages
     * @param list<OfferCityTax>                   $cityTaxes
     */
    public function __construct(
        public \DateTimeImmutable $arrival,
        public \DateTimeImmutable $departure,
        public GuaranteeType $minGuaranteeType,
        public int $availableUnits,
        public MonetaryValue $totalGrossAmount,
        public OfferCancellationFee $cancellationFee,
        public OfferNoShowFee $noShowFee,
        public array $timeSlices,
        public array $services,
        public array $fees,
        public array $taxDetails,
        public array $validationMessages,
        public ?string $companyId,
        public ?string $corporateCode,
        public bool $isCorporate,
        public array $cityTaxes,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            arrival: ResponseData::dateTime($data, 'arrival'),
            departure: ResponseData::dateTime($data, 'departure'),
            minGuaranteeType: GuaranteeType::fromApi(ResponseData::string($data, 'minGuaranteeType')),
            availableUnits: ResponseData::int($data, 'availableUnits'),
            totalGrossAmount: ResponseData::requiredNested($data, 'totalGrossAmount', MonetaryValue::fromArray(...)),
            cancellationFee: ResponseData::requiredNested($data, 'cancellationFee', OfferCancellationFee::fromArray(...)),
            noShowFee: ResponseData::requiredNested($data, 'noShowFee', OfferNoShowFee::fromArray(...)),
            timeSlices: ResponseData::mapList($data, 'timeSlices', ReservationStayOfferTimeSlice::fromArray(...)),
            services: ResponseData::mapList($data, 'services', ServiceOffer::fromArray(...)),
            fees: ResponseData::mapList($data, 'fees', OfferFee::fromArray(...)),
            taxDetails: ResponseData::mapList($data, 'taxDetails', TaxDetail::fromArray(...)),
            validationMessages: ResponseData::mapList($data, 'validationMessages', OfferValidationMessage::fromArray(...)),
            companyId: ResponseData::nullableString($data, 'companyId'),
            corporateCode: ResponseData::nullableString($data, 'corporateCode'),
            isCorporate: ResponseData::bool($data, 'isCorporate'),
            cityTaxes: ResponseData::mapList($data, 'cityTaxes', OfferCityTax::fromArray(...)),
        );
    }
}
