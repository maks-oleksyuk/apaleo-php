<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum\CityTaxType;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum\TaxHandlingType;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Item shape of GET /settings/v1/city-tax: unlike CityTax, $name and $description are plain strings, and limit, subcategories, pricing rules and ignoredFor are missing. */
final readonly class CityTaxListItem
{
    /** @param float $value percent or multiplier, depending on $type */
    public function __construct(
        public string $id,
        public string $code,
        public string $propertyId,
        public string $name,
        public string $description,
        public CityTaxType $type,
        public TaxHandlingType $taxHandlingType,
        public float $value,
        public VatType $vatType,
        public int $priority,
        public bool $includeCityTaxInRateAmount,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            propertyId: ResponseData::string($data, 'propertyId'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::string($data, 'description'),
            type: CityTaxType::fromApi(ResponseData::string($data, 'type')),
            taxHandlingType: TaxHandlingType::fromApi(ResponseData::string($data, 'taxHandlingType')),
            value: ResponseData::float($data, 'value'),
            vatType: VatType::fromApi(ResponseData::string($data, 'vatType')),
            priority: ResponseData::int($data, 'priority'),
            includeCityTaxInRateAmount: ResponseData::bool($data, 'includeCityTaxInRateAmount'),
        );
    }
}
