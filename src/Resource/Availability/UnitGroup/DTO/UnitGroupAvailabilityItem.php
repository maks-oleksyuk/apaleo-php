<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\UnitGroup\DTO;

use Oleksyuk\Apaleo\Resource\Availability\Shared\DTO\BlockCounts;
use Oleksyuk\Apaleo\Resource\Availability\Shared\DTO\Maintenance;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedUnitGroup;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class UnitGroupAvailabilityItem
{
    public function __construct(
        public EmbeddedUnitGroup $unitGroup,
        public int $physicalCount,
        public int $houseCount,
        public int $soldCount,
        public float $occupancy,
        public int $availableCount,
        public int $sellableCount,
        public int $allowedOverbookingCount,
        public Maintenance $maintenance,
        public BlockCounts $block,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            unitGroup: EmbeddedUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
            physicalCount: ResponseData::int($data, 'physicalCount'),
            houseCount: ResponseData::int($data, 'houseCount'),
            soldCount: ResponseData::int($data, 'soldCount'),
            occupancy: ResponseData::float($data, 'occupancy'),
            availableCount: ResponseData::int($data, 'availableCount'),
            sellableCount: ResponseData::int($data, 'sellableCount'),
            allowedOverbookingCount: ResponseData::int($data, 'allowedOverbookingCount'),
            maintenance: Maintenance::fromArray(ResponseData::nested($data, 'maintenance')),
            block: BlockCounts::fromArray(ResponseData::nested($data, 'block')),
        );
    }
}
