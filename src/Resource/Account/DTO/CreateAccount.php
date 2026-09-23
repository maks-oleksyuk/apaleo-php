<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Account\DTO;

use Oleksyuk\Apaleo\Resource\Account\Enum\AccountType;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\Address;

final readonly class CreateAccount
{
    public function __construct(
        public string $name,
        public string $defaultLanguage,
        public AccountType $type,
        public ?string $code = null,
        public ?string $description = null,
        public ?string $logoUrl = null,
        public ?Address $location = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'defaultLanguage' => $this->defaultLanguage,
            'logoUrl' => $this->logoUrl,
            'type' => $this->type->value,
            'location' => $this->location?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
