<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PropertyPerformanceReport
{
    /** @param list<PropertyPerformanceReportItem> $businessDays */
    public function __construct(
        public PerformanceMetrics $metrics,
        public array $businessDays,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            metrics: PerformanceMetrics::fromArray($data),
            businessDays: array_map(PropertyPerformanceReportItem::fromArray(...), ResponseData::nestedList($data, 'businessDays')),
        );
    }
}
