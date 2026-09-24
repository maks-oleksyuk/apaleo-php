<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum\CityTaxType;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum\TaxHandlingType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CityTax
{
    /**
     * @param array<string, string> $name
     * @param array<string, string> $description
     * @param float $value percent or multiplier, depending on $type
     * @param ?int $limit number of time slices the tax is charged for, counted from arrival
     * @param list<CityTaxSubcategory> $subcategories
     * @param list<CityTaxPricingRule> $pricingRules
     * @param list<CityTaxIgnoreRule> $ignoredFor
     */
    public function __construct(
        public string $id,
        public string $code,
        public string $propertyId,
        public array $name,
        public array $description,
        public CityTaxType $type,
        public TaxHandlingType $taxHandlingType,
        public float $value,
        public VatType $vatType,
        public int $priority,
        public bool $includeCityTaxInRateAmount,
        public ?int $limit,
        public array $subcategories,
        public array $pricingRules,
        public array $ignoredFor,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            propertyId: ResponseData::string($data, 'propertyId'),
            name: ResponseData::localizedText($data, 'name'),
            description: ResponseData::localizedText($data, 'description'),
            type: CityTaxType::fromApi(ResponseData::string($data, 'type')),
            taxHandlingType: TaxHandlingType::fromApi(ResponseData::string($data, 'taxHandlingType')),
            value: ResponseData::float($data, 'value'),
            vatType: VatType::fromApi(ResponseData::string($data, 'vatType')),
            priority: ResponseData::int($data, 'priority'),
            includeCityTaxInRateAmount: ResponseData::bool($data, 'includeCityTaxInRateAmount'),
            limit: ResponseData::nullableInt($data, 'limit'),
            subcategories: array_map(CityTaxSubcategory::fromArray(...), ResponseData::nestedList($data, 'subcategories')),
            pricingRules: array_map(CityTaxPricingRule::fromArray(...), ResponseData::nestedList($data, 'pricingRules')),
            ignoredFor: array_map(CityTaxIgnoreRule::fromArray(...), ResponseData::nestedList($data, 'ignoredFor')),
        );
    }
}
