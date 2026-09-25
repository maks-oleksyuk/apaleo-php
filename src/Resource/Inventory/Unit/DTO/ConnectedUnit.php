<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\DTO;

use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitCondition;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ConnectedUnit
{
    public function __construct(
        public string $id,
        public string $name,
        public string $description,
        public string $unitGroupId,
        public UnitCondition $condition,
        public string $rawCondition,
        public int $maxPersons,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $condition = ResponseData::string($data, 'condition');

        return new self(
            id: ResponseData::string($data, 'id'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::string($data, 'description'),
            unitGroupId: ResponseData::string($data, 'unitGroupId'),
            condition: UnitCondition::fromApi($condition),
            rawCondition: $condition,
            maxPersons: ResponseData::int($data, 'maxPersons'),
        );
    }
}
