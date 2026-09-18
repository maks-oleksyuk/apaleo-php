<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\DTO;

final readonly class CreateUnitAttributeDefinition
{
    public function __construct(
        public string $name,
        public ?string $description = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
