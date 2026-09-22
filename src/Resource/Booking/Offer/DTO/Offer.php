<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedRatePlan;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\TaxDetail;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Offer
{
    /**
     * @param list<OfferTimeSlice>         $timeSlices
     * @param list<ServiceOffer>           $services
     * @param list<OfferFee>               $fees
     * @param list<TaxDetail>              $taxDetails
     * @param list<OfferValidationMessage> $validationMessages
     * @param list<OfferCityTax>           $cityTaxes
     */
    public function __construct(
        public \DateTimeImmutable $arrival,
        public \DateTimeImmutable $departure,
        public OfferUnitGroup $unitGroup,
        public GuaranteeType $minGuaranteeType,
        public int $availableUnits,
        public EmbeddedRatePlan $ratePlan,
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
        public MonetaryValue $prePaymentAmount,
        public array $cityTaxes,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            arrival: ResponseData::dateTime($data, 'arrival'),
            departure: ResponseData::dateTime($data, 'departure'),
            unitGroup: OfferUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
            minGuaranteeType: GuaranteeType::fromApi(ResponseData::string($data, 'minGuaranteeType')),
            availableUnits: ResponseData::int($data, 'availableUnits'),
            ratePlan: EmbeddedRatePlan::fromArray(ResponseData::nested($data, 'ratePlan')),
            totalGrossAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'totalGrossAmount')),
            cancellationFee: OfferCancellationFee::fromArray(ResponseData::nested($data, 'cancellationFee')),
            noShowFee: OfferNoShowFee::fromArray(ResponseData::nested($data, 'noShowFee')),
            timeSlices: array_map(OfferTimeSlice::fromArray(...), ResponseData::nestedList($data, 'timeSlices')),
            services: array_map(ServiceOffer::fromArray(...), ResponseData::nestedList($data, 'services')),
            fees: array_map(OfferFee::fromArray(...), ResponseData::nestedList($data, 'fees')),
            taxDetails: array_map(TaxDetail::fromArray(...), ResponseData::nestedList($data, 'taxDetails')),
            validationMessages: array_map(OfferValidationMessage::fromArray(...), ResponseData::nestedList($data, 'validationMessages')),
            companyId: ResponseData::nullableString($data, 'companyId'),
            corporateCode: ResponseData::nullableString($data, 'corporateCode'),
            isCorporate: ResponseData::bool($data, 'isCorporate'),
            prePaymentAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'prePaymentAmount')),
            cityTaxes: array_map(OfferCityTax::fromArray(...), ResponseData::nestedList($data, 'cityTaxes')),
        );
    }
}
