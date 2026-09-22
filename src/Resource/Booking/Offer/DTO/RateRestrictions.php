<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class RateRestrictions
{
    public function __construct(
        public ?int $minLengthOfStay,
        public ?int $maxLengthOfStay,
        public bool $closed,
        public bool $closedOnArrival,
        public bool $closedOnDeparture,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            minLengthOfStay: ResponseData::nullableInt($data, 'minLengthOfStay'),
            maxLengthOfStay: ResponseData::nullableInt($data, 'maxLengthOfStay'),
            closed: ResponseData::bool($data, 'closed'),
            closedOnArrival: ResponseData::bool($data, 'closedOnArrival'),
            closedOnDeparture: ResponseData::bool($data, 'closedOnDeparture'),
        );
    }
}
