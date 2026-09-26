<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations;

use Oleksyuk\Apaleo\Resource\Shared\Enum\MaintenanceType;
use Oleksyuk\Apaleo\Support\Query;

final readonly class MaintenanceFilter
{
    /** @param list<MaintenanceType> $types */
    public function __construct(
        public ?string $propertyId = null,
        public ?string $unitId = null,
        public ?\DateTimeImmutable $from = null,
        public ?\DateTimeImmutable $to = null,
        public array $types = [],
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'unitId' => $this->unitId,
            'from' => $this->from?->format(\DateTimeInterface::ATOM),
            'to' => $this->to?->format(\DateTimeInterface::ATOM),
            'types' => Query::csv($this->types),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
