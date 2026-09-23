<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Service\Enum\PricingUnit;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\AccountingConfig;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\ServiceType;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Support\ResponseData;

/**
 * The single GET returns the full $accountingConfigs history; the list only returns the
 * currently valid one, flattened into $serviceType / $vatType / $subAccountId.
 */
final readonly class Service
{
    /**
     * @param array<string, string> $name localized, or ['default' => ...] when the endpoint returns a plain string
     * @param array<string, string> $description
     * @param list<AccountingConfig> $accountingConfigs
     * @param list<ChannelCode> $channelCodes
     */
    public function __construct(
        public string $id,
        public string $code,
        public array $name,
        public array $description,
        public MonetaryValue $defaultGrossPrice,
        public ?PricingUnit $pricingUnit,
        public bool $postNextDay,
        public ServiceAvailability $availability,
        public EmbeddedProperty $property,
        public array $accountingConfigs,
        public array $channelCodes,
        public ?string $ageCategoryId,
        public ?ServiceType $serviceType,
        public ?VatType $vatType,
        public ?string $subAccountId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $pricingUnit = ResponseData::nullableString($data, 'pricingUnit');
        $serviceType = ResponseData::nullableString($data, 'serviceType');
        $vatType = ResponseData::nullableString($data, 'vatType');

        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::localizedText($data, 'name'),
            description: ResponseData::localizedText($data, 'description'),
            defaultGrossPrice: MonetaryValue::fromArray(ResponseData::nested($data, 'defaultGrossPrice')),
            pricingUnit: $pricingUnit !== null ? PricingUnit::fromApi($pricingUnit) : null,
            postNextDay: ResponseData::bool($data, 'postNextDay'),
            availability: ServiceAvailability::fromArray(ResponseData::nested($data, 'availability')),
            property: EmbeddedProperty::fromArray(ResponseData::nested($data, 'property')),
            accountingConfigs: array_map(AccountingConfig::fromArray(...), ResponseData::nestedList($data, 'accountingConfigs')),
            channelCodes: array_map(ChannelCode::fromApi(...), ResponseData::stringListOrEmpty($data, 'channelCodes')),
            ageCategoryId: ResponseData::nullableString($data, 'ageCategoryId'),
            serviceType: $serviceType !== null ? ServiceType::fromApi($serviceType) : null,
            vatType: $vatType !== null ? VatType::fromApi($vatType) : null,
            subAccountId: ResponseData::nullableString($data, 'subAccountId'),
        );
    }
}
