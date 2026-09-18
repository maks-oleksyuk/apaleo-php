<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO;

/** Full replacement payload for PUT /unit-groups/{id}. */
final readonly class ReplaceUnitGroup
{
    /**
     * @param array<string, string> $name localized
     * @param array<string, string> $description localized
     * @param array<string, int> $connectedUnitGroups map of unitGroupId => memberCount
     */
    public function __construct(
        public array $name,
        public array $description,
        public ?int $maxPersons = null,
        public ?int $rank = null,
        public array $connectedUnitGroups = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'maxPersons' => $this->maxPersons,
            'rank' => $this->rank,
            'connectedUnitGroups' => array_map(
                static fn (string $unitGroupId, int $memberCount): array => ['unitGroupId' => $unitGroupId, 'memberCount' => $memberCount],
                array_keys($this->connectedUnitGroups),
                array_values($this->connectedUnitGroups),
            ) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
