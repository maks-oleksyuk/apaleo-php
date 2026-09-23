<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\PriceAdjustmentType;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Derives this rate plan's prices from a base rate plan. */
final readonly class PricingRule
{
    public function __construct(
        public string $baseRatePlanId,
        public PriceAdjustmentType $type,
        public float $value,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            baseRatePlanId: ResponseData::string(ResponseData::nested($data, 'baseRatePlan'), 'id'),
            type: PriceAdjustmentType::fromApi(ResponseData::string($data, 'type')),
            value: ResponseData::float($data, 'value'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['baseRatePlanId' => $this->baseRatePlanId, 'type' => $this->type->value, 'value' => $this->value];
    }
}
