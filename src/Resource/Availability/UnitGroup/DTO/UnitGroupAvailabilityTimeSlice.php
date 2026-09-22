<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\UnitGroup\DTO;

use Oleksyuk\Apaleo\Resource\Availability\Property\DTO\PropertyAvailability;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class UnitGroupAvailabilityTimeSlice
{
    /** @param list<UnitGroupAvailabilityItem> $unitGroups */
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public PropertyAvailability $property,
        public array $unitGroups,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            property: PropertyAvailability::fromArray(ResponseData::nested($data, 'property')),
            unitGroups: array_map(UnitGroupAvailabilityItem::fromArray(...), ResponseData::nestedList($data, 'unitGroups')),
        );
    }
}
