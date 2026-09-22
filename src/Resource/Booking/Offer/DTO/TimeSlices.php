<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** The offer-index endpoint's response: this rate plan's offers across every time slice in the requested range. */
final readonly class TimeSlices
{
    /** @param list<TimeSliceItem> $timeSlices */
    public function __construct(
        public array $timeSlices,
        public int $count,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            timeSlices: array_map(TimeSliceItem::fromArray(...), ResponseData::nestedList($data, 'timeSlices')),
            count: ResponseData::int($data, 'count'),
        );
    }
}
