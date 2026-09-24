<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\VatType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class TaxAmount
{
    public function __construct(
        public VatType $type,
        public float $percent,
        public float $amount,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: VatType::fromApi(ResponseData::string($data, 'type')),
            percent: ResponseData::float($data, 'percent'),
            amount: ResponseData::float($data, 'amount'),
        );
    }
}
