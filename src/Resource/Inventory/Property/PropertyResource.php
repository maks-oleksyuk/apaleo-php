<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\Property;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\GetPropertyRequest;
use Oleksyuk\Apaleo\Resource\Inventory\Property\Requests\ListPropertiesRequest;

final readonly class PropertyResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {
    }

    public function get(string $propertyId): Property
    {
        $data = $this->pipeline->send(new GetPropertyRequest($propertyId));

        return Property::fromArray($data);
    }

    /**
     * @return list<Property>
     */
    public function list(): array
    {
        $data = $this->pipeline->send(new ListPropertiesRequest());
        $items = \is_array($data['properties'] ?? null) ? $data['properties'] : [];

        return array_values(array_map(
            /** @param mixed $item */
            static function ($item): Property {
                /** @var array<string, mixed> $itemData */
                $itemData = \is_array($item) ? $item : [];

                return Property::fromArray($itemData);
            },
            $items,
        ));
    }
}
