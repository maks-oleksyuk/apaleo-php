<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedUnit;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ReservationAssignedUnit
{
    /** @param list<AssignedUnitTimeRange> $timeRanges */
    public function __construct(
        public EmbeddedUnit $unit,
        public array $timeRanges,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            unit: ResponseData::requiredNested($data, 'unit', EmbeddedUnit::fromArray(...)),
            timeRanges: ResponseData::mapList($data, 'timeRanges', AssignedUnitTimeRange::fromArray(...)),
        );
    }
}
