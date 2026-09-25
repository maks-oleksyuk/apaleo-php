<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\PriceCalculationMode;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\AccountingConfig;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedMarketSegment;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedUnitGroup;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class RatePlan
{
    /**
     * @param array<string, string> $name
     * @param array<string, string> $description
     * @param list<ChannelCode> $channelCodes
     * @param list<string> $promoCodes
     * @param list<BookingPeriod> $bookingPeriods
     * @param list<Surcharge> $surcharges
     * @param list<RatePlanAgeCategory> $ageCategories
     * @param list<IncludedService> $includedServices
     * @param list<RatePlanCompany> $companies
     * @param list<AccountingConfig> $accountingConfigs
     */
    public function __construct(
        public string $id,
        public string $code,
        public array $name,
        public array $description,
        public GuaranteeType $minGuaranteeType,
        public ?PriceCalculationMode $priceCalculationMode,
        public EmbeddedProperty $property,
        public EmbeddedUnitGroup $unitGroup,
        public EmbeddedCancellationPolicy $cancellationPolicy,
        public ?EmbeddedNoShowPolicy $noShowPolicy,
        public EmbeddedTimeSliceDefinition $timeSliceDefinition,
        public array $channelCodes,
        public array $promoCodes,
        public ?BookingRestrictions $restrictions,
        public array $bookingPeriods,
        public bool $isBookable,
        public bool $isSubjectToCityTax,
        public ?PricingRule $pricingRule,
        public bool $isDerived,
        public int $derivationLevel,
        public array $surcharges,
        public array $ageCategories,
        public array $includedServices,
        public array $companies,
        public ?RatesRange $ratesRange,
        public array $accountingConfigs,
        public ?EmbeddedMarketSegment $marketSegment,
        public bool $isArchived,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $priceCalculationMode = ResponseData::nullableString($data, 'priceCalculationMode');
        $noShowPolicy = ResponseData::nested($data, 'noShowPolicy');
        $restrictions = ResponseData::nested($data, 'restrictions');
        $pricingRule = ResponseData::nested($data, 'pricingRule');
        $ratesRange = ResponseData::nested($data, 'ratesRange');
        $marketSegment = ResponseData::nested($data, 'marketSegment');

        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::localizedText($data, 'name'),
            description: ResponseData::localizedText($data, 'description'),
            minGuaranteeType: GuaranteeType::fromApi(ResponseData::string($data, 'minGuaranteeType')),
            priceCalculationMode: $priceCalculationMode !== null ? PriceCalculationMode::fromApi($priceCalculationMode) : null,
            property: EmbeddedProperty::fromArray(ResponseData::nested($data, 'property')),
            unitGroup: EmbeddedUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
            cancellationPolicy: EmbeddedCancellationPolicy::fromArray(ResponseData::nested($data, 'cancellationPolicy')),
            noShowPolicy: $noShowPolicy !== [] ? EmbeddedNoShowPolicy::fromArray($noShowPolicy) : null,
            timeSliceDefinition: EmbeddedTimeSliceDefinition::fromArray(ResponseData::nested($data, 'timeSliceDefinition')),
            channelCodes: array_map(ChannelCode::fromApi(...), ResponseData::stringListOrEmpty($data, 'channelCodes')),
            promoCodes: ResponseData::stringListOrEmpty($data, 'promoCodes'),
            restrictions: $restrictions !== [] ? BookingRestrictions::fromArray($restrictions) : null,
            bookingPeriods: array_map(BookingPeriod::fromArray(...), ResponseData::nestedList($data, 'bookingPeriods')),
            isBookable: ResponseData::bool($data, 'isBookable'),
            isSubjectToCityTax: ResponseData::bool($data, 'isSubjectToCityTax'),
            pricingRule: $pricingRule !== [] ? PricingRule::fromArray($pricingRule) : null,
            isDerived: ResponseData::bool($data, 'isDerived'),
            derivationLevel: ResponseData::nullableInt($data, 'derivationLevel') ?? 0,
            surcharges: array_map(Surcharge::fromArray(...), ResponseData::nestedList($data, 'surcharges')),
            ageCategories: array_map(RatePlanAgeCategory::fromArray(...), ResponseData::nestedList($data, 'ageCategories')),
            includedServices: array_map(IncludedService::fromArray(...), ResponseData::nestedList($data, 'includedServices')),
            companies: array_map(RatePlanCompany::fromArray(...), ResponseData::nestedList($data, 'companies')),
            ratesRange: $ratesRange !== [] ? RatesRange::fromArray($ratesRange) : null,
            accountingConfigs: array_map(AccountingConfig::fromArray(...), ResponseData::nestedList($data, 'accountingConfigs')),
            marketSegment: $marketSegment !== [] ? EmbeddedMarketSegment::fromArray($marketSegment) : null,
            isArchived: ResponseData::bool($data, 'isArchived'),
        );
    }
}
