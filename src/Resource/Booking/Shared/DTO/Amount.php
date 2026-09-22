<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Amount
{
    public function __construct(
        public float $grossAmount,
        public float $netAmount,
        public VatType $vatType,
        public float $vatPercent,
        public string $currency,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            grossAmount: ResponseData::float($data, 'grossAmount'),
            netAmount: ResponseData::float($data, 'netAmount'),
            vatType: VatType::fromApi(ResponseData::string($data, 'vatType')),
            vatPercent: ResponseData::float($data, 'vatPercent'),
            currency: ResponseData::string($data, 'currency'),
        );
    }
}
