<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PersonCompany
{
    public function __construct(
        public ?string $name,
        public ?string $taxId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: ResponseData::nullableString($data, 'name'),
            taxId: ResponseData::nullableString($data, 'taxId'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'taxId' => $this->taxId,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
