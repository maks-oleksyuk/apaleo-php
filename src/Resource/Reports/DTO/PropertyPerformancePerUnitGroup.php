<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Resource\Availability\Shared\DTO\EmbeddedUnitGroup;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PropertyPerformancePerUnitGroup
{
    public function __construct(
        public EmbeddedUnitGroup $unitGroup,
        public PerformanceMetrics $metrics,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            unitGroup: EmbeddedUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
            metrics: PerformanceMetrics::fromArray($data),
        );
    }
}
