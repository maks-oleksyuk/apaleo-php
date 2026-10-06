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

final readonly class RatePlanListItem
{
    /**
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
        public string $name,
        public string $description,
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

        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::string($data, 'description'),
            minGuaranteeType: GuaranteeType::fromApi(ResponseData::string($data, 'minGuaranteeType')),
            priceCalculationMode: $priceCalculationMode !== null ? PriceCalculationMode::fromApi($priceCalculationMode) : null,
            property: ResponseData::requiredNested($data, 'property', EmbeddedProperty::fromArray(...)),
            unitGroup: ResponseData::requiredNested($data, 'unitGroup', EmbeddedUnitGroup::fromArray(...)),
            cancellationPolicy: ResponseData::requiredNested($data, 'cancellationPolicy', EmbeddedCancellationPolicy::fromArray(...)),
            noShowPolicy: ResponseData::nullableNested($data, 'noShowPolicy', EmbeddedNoShowPolicy::fromArray(...)),
            timeSliceDefinition: ResponseData::requiredNested($data, 'timeSliceDefinition', EmbeddedTimeSliceDefinition::fromArray(...)),
            channelCodes: array_map(ChannelCode::fromApi(...), ResponseData::stringListOrEmpty($data, 'channelCodes')),
            promoCodes: ResponseData::stringListOrEmpty($data, 'promoCodes'),
            restrictions: ResponseData::nullableNested($data, 'restrictions', BookingRestrictions::fromArray(...)),
            bookingPeriods: ResponseData::mapList($data, 'bookingPeriods', BookingPeriod::fromArray(...)),
            isBookable: ResponseData::bool($data, 'isBookable'),
            isSubjectToCityTax: ResponseData::bool($data, 'isSubjectToCityTax'),
            pricingRule: ResponseData::nullableNested($data, 'pricingRule', PricingRule::fromArray(...)),
            isDerived: ResponseData::bool($data, 'isDerived'),
            derivationLevel: ResponseData::nullableInt($data, 'derivationLevel') ?? 0,
            surcharges: ResponseData::mapList($data, 'surcharges', Surcharge::fromArray(...)),
            ageCategories: ResponseData::mapList($data, 'ageCategories', RatePlanAgeCategory::fromArray(...)),
            includedServices: ResponseData::mapList($data, 'includedServices', IncludedService::fromArray(...)),
            companies: ResponseData::mapList($data, 'companies', RatePlanCompany::fromArray(...)),
            ratesRange: ResponseData::nullableNested($data, 'ratesRange', RatesRange::fromArray(...)),
            accountingConfigs: ResponseData::mapList($data, 'accountingConfigs', AccountingConfig::fromArray(...)),
            marketSegment: ResponseData::nullableNested($data, 'marketSegment', EmbeddedMarketSegment::fromArray(...)),
            isArchived: ResponseData::bool($data, 'isArchived'),
        );
    }
}
