<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\Property\PropertyResource;
use Oleksyuk\Apaleo\Resource\Inventory\Types\Country\CountryResource;
use Oleksyuk\Apaleo\Resource\Inventory\Unit\UnitResource;
use Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\UnitAttributeResource;
use Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\UnitGroupResource;

final readonly class InventoryResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @throws ApaleoExceptionInterface
     */
    public function properties(): PropertyResource
    {
        return new PropertyResource($this->pipeline);
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
    public function unitGroups(): UnitGroupResource
    {
        return new UnitGroupResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function unitAttributes(): UnitAttributeResource
    {
        return new UnitAttributeResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function countries(): CountryResource
    {
        return new CountryResource($this->pipeline);
    }
}
