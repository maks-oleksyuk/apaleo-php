<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO;

use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;

final readonly class CreateUnitGroup
{
    /**
     * @param array<string, string> $name localized
     * @param array<string, string> $description localized
     */
    public function __construct(
        public string $code,
        public string $propertyId,
        public array $name,
        public array $description,
        public int $maxPersons,
        public ?int $rank = null,
        public ?UnitGroupType $type = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'propertyId' => $this->propertyId,
            'name' => $this->name,
            'description' => $this->description,
            'maxPersons' => $this->maxPersons,
            'rank' => $this->rank,
            'type' => $this->type?->value,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
