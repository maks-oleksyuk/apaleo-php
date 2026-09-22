<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Property;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\Property\DTO\HouseOverbookingTimeSlice;
use Oleksyuk\Apaleo\Resource\Availability\Property\Requests\GetHouseOverbookingRequest;
use Oleksyuk\Apaleo\Resource\Availability\Property\Requests\UpdateHouseOverbookingRequest;
use Oleksyuk\Apaleo\Resource\Availability\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Resource\Availability\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PropertyResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @return list<HouseOverbookingTimeSlice> the time slices in [$from, $to) where house-level overbooking is applied */
    public function houseOverbooking(
        string $propertyId,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        TimeSliceTemplate $timeSliceTemplate = TimeSliceTemplate::OverNight,
        UnitGroupType $unitGroupType = UnitGroupType::BedRoom,
    ): array {
        $data = $this->pipeline->send(new GetHouseOverbookingRequest($propertyId, $from, $to, $timeSliceTemplate, $unitGroupType));

        return array_map(HouseOverbookingTimeSlice::fromArray(...), ResponseData::nestedList($data, 'timeSlices'));
    }

    /** Replaces the house-level overbooking limit for [$from, $to) — e.g. `(new JsonPatch())->replace('/houseOverbookingLimit', 5)`. */
    public function updateHouseOverbooking(
        string $propertyId,
        \DateTimeImmutable $from,
        \DateTimeImmutable $to,
        JsonPatch $patch,
        TimeSliceTemplate $timeSliceTemplate = TimeSliceTemplate::OverNight,
        UnitGroupType $unitGroupType = UnitGroupType::BedRoom,
    ): void {
        $this->pipeline->send(new UpdateHouseOverbookingRequest($propertyId, $from, $to, $timeSliceTemplate, $unitGroupType, $patch));
    }
}
