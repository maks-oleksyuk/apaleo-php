<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Price
{
    public function __construct(
        public float $beforeTax,
        public float $afterTax,
        public Taxes $taxes,
        public string $currency,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            beforeTax: ResponseData::float($data, 'beforeTax'),
            afterTax: ResponseData::float($data, 'afterTax'),
            taxes: Taxes::fromArray(ResponseData::nested($data, 'taxes')),
            currency: ResponseData::string($data, 'currency'),
        );
    }
}
