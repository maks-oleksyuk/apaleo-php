<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Service\Enum\PricingUnit;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ServiceType;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Support\ResponseData;

/**
 * Item shape of GET /rateplan/v1/services: unlike Service, $name and $description are plain strings,
 * and instead of the $accountingConfigs history only the currently valid one is returned, flattened
 * into $serviceType / $vatType / $subAccountId.
 */
final readonly class ServiceListItem
{
    /** @param list<ChannelCode> $channelCodes */
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public string $description,
        public MonetaryValue $defaultGrossPrice,
        public ?PricingUnit $pricingUnit,
        public bool $postNextDay,
        public ServiceType $serviceType,
        public VatType $vatType,
        public ?string $subAccountId,
        public ServiceAvailability $availability,
        public EmbeddedProperty $property,
        public array $channelCodes,
        public ?string $ageCategoryId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $pricingUnit = ResponseData::nullableString($data, 'pricingUnit');

        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::string($data, 'description'),
            defaultGrossPrice: MonetaryValue::fromArray(ResponseData::nested($data, 'defaultGrossPrice')),
            pricingUnit: $pricingUnit !== null ? PricingUnit::fromApi($pricingUnit) : null,
            postNextDay: ResponseData::bool($data, 'postNextDay'),
            serviceType: ServiceType::fromApi(ResponseData::string($data, 'serviceType')),
            vatType: VatType::fromApi(ResponseData::string($data, 'vatType')),
            subAccountId: ResponseData::nullableString($data, 'subAccountId'),
            availability: ServiceAvailability::fromArray(ResponseData::nested($data, 'availability')),
            property: EmbeddedProperty::fromArray(ResponseData::nested($data, 'property')),
            channelCodes: array_map(ChannelCode::fromApi(...), ResponseData::stringListOrEmpty($data, 'channelCodes')),
            ageCategoryId: ResponseData::nullableString($data, 'ageCategoryId'),
        );
    }
}
