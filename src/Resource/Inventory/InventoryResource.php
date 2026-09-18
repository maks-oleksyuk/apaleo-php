<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\Property\PropertyResource;

/** Aggregates the Inventory API's sub-resources (properties, units, unit groups, ...). */
final readonly class InventoryResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {
    }

    public function properties(): PropertyResource
    {
        return new PropertyResource($this->pipeline);
    }
}
