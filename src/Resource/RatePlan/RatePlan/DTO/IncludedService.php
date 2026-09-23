<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\PricingMode;
use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class IncludedService
{
    public function __construct(
        public string $serviceId,
        public MonetaryValue $grossPrice,
        public ?PricingMode $pricingMode = null,
    ) {}

    /**
     * The single GET returns `serviceId`, the list returns an embedded `service: {id, ...}` instead.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $service = ResponseData::nested($data, 'service');
        $pricingMode = ResponseData::nullableString($data, 'pricingMode');

        return new self(
            serviceId: $service !== [] ? ResponseData::string($service, 'id') : ResponseData::string($data, 'serviceId'),
            grossPrice: MonetaryValue::fromArray(ResponseData::nested($data, 'grossPrice')),
            pricingMode: $pricingMode !== null ? PricingMode::fromApi($pricingMode) : null,
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'serviceId' => $this->serviceId,
            'grossPrice' => $this->grossPrice->toArray(),
            'pricingMode' => $this->pricingMode?->value,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
