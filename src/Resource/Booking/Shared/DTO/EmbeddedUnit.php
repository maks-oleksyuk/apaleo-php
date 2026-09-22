<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class EmbeddedUnit
{
    public function __construct(
        public string $id,
        public ?string $name,
        public ?string $description,
        public ?string $unitGroupId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            name: ResponseData::nullableString($data, 'name'),
            description: ResponseData::nullableString($data, 'description'),
            unitGroupId: ResponseData::nullableString($data, 'unitGroupId'),
        );
    }
}
