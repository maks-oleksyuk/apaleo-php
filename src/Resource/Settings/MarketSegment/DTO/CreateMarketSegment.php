<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\MarketSegment\DTO;

final readonly class CreateMarketSegment
{
    /** @param list<string> $propertyIds */
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description = null,
        public array $propertyIds = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'propertyIds' => $this->propertyIds ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
