<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO;

use Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum\CityTaxType;
use Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum\TaxHandlingType;
use Oleksyuk\Apaleo\Resource\Shared\Enum\VatType;

final readonly class CreateCityTax
{
    /**
     * @param array<string, string> $name localized
     * @param array<string, string> $description localized
     * @param float $value percent or multiplier, depending on $type
     * @param ?int $priority lower is applied first; apaleo appends it last when omitted
     * @param list<CityTaxSubcategory> $subcategories
     * @param list<CityTaxPricingRule> $pricingRules
     * @param list<CityTaxIgnoreRule> $ignoredFor
     */
    public function __construct(
        public string $propertyId,
        public array $name,
        public array $description,
        public CityTaxType $type,
        public TaxHandlingType $taxHandlingType,
        public float $value,
        public VatType $vatType,
        public ?string $code = null,
        public ?int $priority = null,
        public ?bool $includeCityTaxInRateAmount = null,
        public ?int $limit = null,
        public array $subcategories = [],
        public array $pricingRules = [],
        public array $ignoredFor = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type->value,
            'taxHandlingType' => $this->taxHandlingType->value,
            'value' => $this->value,
            'vatType' => $this->vatType->value,
            'code' => $this->code,
            'priority' => $this->priority,
            'includeCityTaxInRateAmount' => $this->includeCityTaxInRateAmount,
            'limit' => $this->limit,
            'subcategories' => array_map(static fn (CityTaxSubcategory $s): array => $s->toArray(), $this->subcategories) ?: null,
            'pricingRules' => array_map(static fn (CityTaxPricingRule $r): array => $r->toArray(), $this->pricingRules) ?: null,
            'ignoredFor' => array_map(static fn (CityTaxIgnoreRule $r): array => $r->toArray(), $this->ignoredFor) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
