<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitAttribute\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class UnitAttributeDefinition
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $description,
    ) {
    }

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
