<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Availability\Property\PropertyResource;
use Oleksyuk\Apaleo\Resource\Availability\Service\ServiceResource;
use Oleksyuk\Apaleo\Resource\Availability\Unit\UnitResource;
use Oleksyuk\Apaleo\Resource\Availability\UnitGroup\UnitGroupResource;

final readonly class AvailabilityResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @throws ApaleoExceptionInterface
     */
    public function unitGroups(): UnitGroupResource
    {
        return new UnitGroupResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function units(): UnitResource
    {
        return new UnitResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function services(): ServiceResource
    {
        return new ServiceResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function properties(): PropertyResource
    {
        return new PropertyResource($this->pipeline);
    }
}
