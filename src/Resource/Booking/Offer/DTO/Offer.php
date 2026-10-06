<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedRatePlan;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\TaxDetail;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
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
            unitGroup: ResponseData::requiredNested($data, 'unitGroup', OfferUnitGroup::fromArray(...)),
            minGuaranteeType: GuaranteeType::fromApi(ResponseData::string($data, 'minGuaranteeType')),
            availableUnits: ResponseData::int($data, 'availableUnits'),
            ratePlan: ResponseData::requiredNested($data, 'ratePlan', EmbeddedRatePlan::fromArray(...)),
            totalGrossAmount: ResponseData::requiredNested($data, 'totalGrossAmount', MonetaryValue::fromArray(...)),
            cancellationFee: ResponseData::requiredNested($data, 'cancellationFee', OfferCancellationFee::fromArray(...)),
            noShowFee: ResponseData::requiredNested($data, 'noShowFee', OfferNoShowFee::fromArray(...)),
            timeSlices: ResponseData::mapList($data, 'timeSlices', OfferTimeSlice::fromArray(...)),
            services: ResponseData::mapList($data, 'services', ServiceOffer::fromArray(...)),
            fees: ResponseData::mapList($data, 'fees', OfferFee::fromArray(...)),
            taxDetails: ResponseData::mapList($data, 'taxDetails', TaxDetail::fromArray(...)),
            validationMessages: ResponseData::mapList($data, 'validationMessages', OfferValidationMessage::fromArray(...)),
            companyId: ResponseData::nullableString($data, 'companyId'),
            corporateCode: ResponseData::nullableString($data, 'corporateCode'),
            isCorporate: ResponseData::bool($data, 'isCorporate'),
            prePaymentAmount: ResponseData::requiredNested($data, 'prePaymentAmount', MonetaryValue::fromArray(...)),
            cityTaxes: ResponseData::mapList($data, 'cityTaxes', OfferCityTax::fromArray(...)),
        );
    }
}
