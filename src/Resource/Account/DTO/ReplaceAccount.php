<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Account\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\Address;

/** A full replacement: unset fields are cleared. */
final readonly class ReplaceAccount
{
    /** @param list<string> $additionallySupportedCountries ISO Alpha-2 country codes */
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $logoUrl = null,
        public ?Address $location = null,
        public array $additionallySupportedCountries = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'logoUrl' => $this->logoUrl,
            'location' => $this->location?->toArray(),
            'additionallySupportedCountries' => $this->additionallySupportedCountries ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
