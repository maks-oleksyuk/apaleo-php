<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class MonetaryValue
{
    public function __construct(
        public float $amount,
        public string $currency,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            amount: ResponseData::float($data, 'amount'),
            currency: ResponseData::string($data, 'currency'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['amount' => $this->amount, 'currency' => $this->currency];
    }
}
