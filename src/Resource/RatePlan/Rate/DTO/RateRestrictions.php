<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Rate\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class RateRestrictions
{
    public function __construct(
        public bool $closed = false,
        public bool $closedOnArrival = false,
        public bool $closedOnDeparture = false,
        public ?int $minLengthOfStay = null,
        public ?int $maxLengthOfStay = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            closed: ResponseData::bool($data, 'closed'),
            closedOnArrival: ResponseData::bool($data, 'closedOnArrival'),
            closedOnDeparture: ResponseData::bool($data, 'closedOnDeparture'),
            minLengthOfStay: ResponseData::nullableInt($data, 'minLengthOfStay'),
            maxLengthOfStay: ResponseData::nullableInt($data, 'maxLengthOfStay'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'minLengthOfStay' => $this->minLengthOfStay,
            'maxLengthOfStay' => $this->maxLengthOfStay,
            'closed' => $this->closed,
            'closedOnArrival' => $this->closedOnArrival,
            'closedOnDeparture' => $this->closedOnDeparture,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
