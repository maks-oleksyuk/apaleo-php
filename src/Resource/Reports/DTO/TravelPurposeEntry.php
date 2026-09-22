<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Resource\Reports\Enum\TravelPurpose;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class TravelPurposeEntry
{
    /** @param list<string> $reservationIds */
    public function __construct(
        public ?TravelPurpose $purpose,
        public int $number,
        public float $percent,
        public array $reservationIds,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $purpose = ResponseData::nullableString($data, 'purpose');

        return new self(
            purpose: null !== $purpose ? TravelPurpose::fromApi($purpose) : null,
            number: ResponseData::int($data, 'number'),
            percent: ResponseData::float($data, 'percent'),
            reservationIds: ResponseData::stringList($data, 'reservationIds'),
        );
    }
}
