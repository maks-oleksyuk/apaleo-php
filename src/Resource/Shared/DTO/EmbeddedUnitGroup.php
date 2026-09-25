<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Shared\DTO;

use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class EmbeddedUnitGroup
{
    public function __construct(
        public string $id,
        public ?string $code,
        public ?string $name,
        public ?string $description,
        public ?UnitGroupType $type,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $type = ResponseData::nullableString($data, 'type');

        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::nullableString($data, 'code'),
            name: ResponseData::nullableString($data, 'name'),
            description: ResponseData::nullableString($data, 'description'),
            type: $type !== null ? UnitGroupType::fromApi($type) : null,
        );
    }
}
