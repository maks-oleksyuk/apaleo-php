<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Service\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\AccountingConfig;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Shared\Enum\PricingUnit;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Service
{
    /**
     * @param array<string, string> $name
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
        public PricingUnit $pricingUnit,
        public bool $postNextDay,
        public ServiceAvailability $availability,
        public EmbeddedProperty $property,
        public array $accountingConfigs,
        public array $channelCodes,
        public ?string $ageCategoryId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::localizedText($data, 'name'),
            description: ResponseData::localizedText($data, 'description'),
            defaultGrossPrice: MonetaryValue::fromArray(ResponseData::nested($data, 'defaultGrossPrice')),
            pricingUnit: PricingUnit::fromApi(ResponseData::string($data, 'pricingUnit')),
            postNextDay: ResponseData::bool($data, 'postNextDay'),
            availability: ServiceAvailability::fromArray(ResponseData::nested($data, 'availability')),
            property: EmbeddedProperty::fromArray(ResponseData::nested($data, 'property')),
            accountingConfigs: array_map(AccountingConfig::fromArray(...), ResponseData::nestedList($data, 'accountingConfigs')),
            channelCodes: array_map(ChannelCode::fromApi(...), ResponseData::stringListOrEmpty($data, 'channelCodes')),
            ageCategoryId: ResponseData::nullableString($data, 'ageCategoryId'),
        );
    }
}
