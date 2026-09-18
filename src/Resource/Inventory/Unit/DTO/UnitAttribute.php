<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** An attribute instance attached to a unit (distinct from the attribute's definition). */
final readonly class UnitAttribute
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::nullableString($data, 'description'),
        );
    }
}
