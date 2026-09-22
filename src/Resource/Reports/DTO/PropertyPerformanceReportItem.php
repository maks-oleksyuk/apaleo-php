<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PropertyPerformanceReportItem
{
    /** @param list<PropertyPerformancePerUnitGroup> $unitGroups */
    public function __construct(
        public \DateTimeImmutable $businessDay,
        public PerformanceMetrics $metrics,
        public array $unitGroups,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            businessDay: ResponseData::date($data, 'businessDay'),
            metrics: PerformanceMetrics::fromArray($data),
            unitGroups: array_map(PropertyPerformancePerUnitGroup::fromArray(...), ResponseData::nestedList($data, 'unitGroups')),
        );
    }
}
