<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Property\DTO;

use Oleksyuk\Apaleo\Resource\Availability\Shared\DTO\BlockCounts;
use Oleksyuk\Apaleo\Resource\Availability\Shared\DTO\Maintenance;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PropertyAvailability
{
    public function __construct(
        public int $physicalCount,
        public int $houseCount,
        public int $soldCount,
        public float $occupancy,
        public int $sellableCount,
        public int $allowedOverbookingCount,
        public ?int $houseOverbookingLimit,
        public Maintenance $maintenance,
        public BlockCounts $block,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            physicalCount: ResponseData::int($data, 'physicalCount'),
            houseCount: ResponseData::int($data, 'houseCount'),
            soldCount: ResponseData::int($data, 'soldCount'),
            occupancy: ResponseData::float($data, 'occupancy'),
            sellableCount: ResponseData::int($data, 'sellableCount'),
            allowedOverbookingCount: ResponseData::int($data, 'allowedOverbookingCount'),
            houseOverbookingLimit: ResponseData::nullableInt($data, 'houseOverbookingLimit'),
            maintenance: Maintenance::fromArray(ResponseData::nested($data, 'maintenance')),
            block: BlockCounts::fromArray(ResponseData::nested($data, 'block')),
        );
    }
}
