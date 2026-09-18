<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\Property\PropertyResource;
use Oleksyuk\Apaleo\Resource\Inventory\Types\Country\CountryResource;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\UnitResource;
use Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\UnitAttributeResource;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\UnitGroupResource;

/** Aggregates the Inventory API's sub-resources (properties, units, unit groups, unit attributes, countries). */
final readonly class InventoryResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function properties(): PropertyResource
    {
        return new PropertyResource($this->pipeline);
    }

    public function units(): UnitResource
    {
        return new UnitResource($this->pipeline);
    }

    public function unitGroups(): UnitGroupResource
    {
        return new UnitGroupResource($this->pipeline);
    }

    public function unitAttributes(): UnitAttributeResource
    {
        return new UnitAttributeResource($this->pipeline);
    }

    public function countries(): CountryResource
    {
        return new CountryResource($this->pipeline);
    }
}
