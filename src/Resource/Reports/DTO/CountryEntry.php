<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CountryEntry
{
    /** @param list<string> $reservationIds */
    public function __construct(
        public ?string $countryCode,
        public int $number,
        public float $percent,
        public array $reservationIds,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            countryCode: ResponseData::nullableString($data, 'countryCode'),
            number: ResponseData::int($data, 'number'),
            percent: ResponseData::float($data, 'percent'),
            reservationIds: ResponseData::stringListOrEmpty($data, 'reservationIds'),
        );
    }
}
