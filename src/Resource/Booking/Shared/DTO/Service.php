<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\PricingUnit;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Service
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public string $description,
        public PricingUnit $pricingUnit,
        public MonetaryValue $defaultGrossPrice,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::string($data, 'description'),
            pricingUnit: PricingUnit::fromApi(ResponseData::string($data, 'pricingUnit')),
            defaultGrossPrice: MonetaryValue::fromArray(ResponseData::nested($data, 'defaultGrossPrice')),
        );
    }
}
