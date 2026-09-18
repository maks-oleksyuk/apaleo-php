<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\UnitGroup\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ConnectedUnitGroup
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public int $memberCount,
        public ?int $maxPersons,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::string($data, 'description'),
            memberCount: ResponseData::int($data, 'memberCount'),
            maxPersons: ResponseData::nullableInt($data, 'maxPersons'),
        );
    }
}
