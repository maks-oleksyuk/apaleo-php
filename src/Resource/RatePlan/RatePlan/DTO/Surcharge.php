<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\PriceAdjustmentType;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Price adjustment for a given number of adults, relative to the single-occupancy rate. */
final readonly class Surcharge
{
    public function __construct(
        public int $adults,
        public PriceAdjustmentType $type,
        public float $value,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            adults: ResponseData::int($data, 'adults'),
            type: PriceAdjustmentType::fromApi(ResponseData::string($data, 'type')),
            value: ResponseData::float($data, 'value'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['adults' => $this->adults, 'type' => $this->type->value, 'value' => $this->value];
    }
}
