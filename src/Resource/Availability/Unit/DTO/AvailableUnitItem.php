<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Unit\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedUnitGroup;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class AvailableUnitItem
{
    /** @param list<UnitAttribute> $attributes */
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public EmbeddedProperty $property,
        public ?EmbeddedUnitGroup $unitGroup,
        public AvailableUnitItemStatus $status,
        public int $maxPersons,
        public array $attributes,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::string($data, 'description'),
            property: ResponseData::requiredNested($data, 'property', EmbeddedProperty::fromArray(...)),
            unitGroup: ResponseData::nullableNested($data, 'unitGroup', EmbeddedUnitGroup::fromArray(...)),
            status: ResponseData::requiredNested($data, 'status', AvailableUnitItemStatus::fromArray(...)),
            maxPersons: ResponseData::int($data, 'maxPersons'),
            attributes: ResponseData::mapList($data, 'attributes', UnitAttribute::fromArray(...)),
        );
    }
}
