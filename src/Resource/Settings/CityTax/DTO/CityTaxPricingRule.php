<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** A fixed city tax $value for an accommodation price up to $maxPrice (inclusive). */
final readonly class CityTaxPricingRule
{
    public function __construct(
        public float $value,
        public float $maxPrice,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            value: ResponseData::float($data, 'value'),
            maxPrice: ResponseData::float($data, 'maxPrice'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['value' => $this->value, 'maxPrice' => $this->maxPrice];
    }
}
