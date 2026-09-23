<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations\DTO;

use Oleksyuk\Apaleo\Resource\Operations\Enum\MaintenanceType;

final readonly class CreateMaintenance
{
    public function __construct(
        public string $unitId,
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public MaintenanceType $type,
        public ?string $description = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'unitId' => $this->unitId,
            'from' => $this->from->format(\DateTimeInterface::ATOM),
            'to' => $this->to->format(\DateTimeInterface::ATOM),
            'type' => $this->type->value,
            'description' => $this->description,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
