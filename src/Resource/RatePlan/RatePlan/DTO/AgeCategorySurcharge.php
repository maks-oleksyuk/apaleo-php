<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class AgeCategorySurcharge
{
    public function __construct(
        public int $adults,
        public float $value,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            adults: ResponseData::int($data, 'adults'),
            value: ResponseData::float($data, 'value'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['adults' => $this->adults, 'value' => $this->value];
    }
}
