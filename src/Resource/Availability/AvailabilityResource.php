<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\Property\PropertyResource;
use Oleksyuk\Apaleo\Resource\Availability\Service\ServiceResource;
use Oleksyuk\Apaleo\Resource\Availability\Unit\UnitResource;
use Oleksyuk\Apaleo\Resource\Availability\UnitGroup\UnitGroupResource;

/** Aggregates the Availability API's sub-resources (unit groups, units, services, house-level overbooking). */
final readonly class AvailabilityResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function unitGroups(): UnitGroupResource
    {
        return new UnitGroupResource($this->pipeline);
    }

    public function units(): UnitResource
    {
        return new UnitResource($this->pipeline);
    }

    public function services(): ServiceResource
    {
        return new ServiceResource($this->pipeline);
    }

    public function properties(): PropertyResource
    {
        return new PropertyResource($this->pipeline);
    }
}
