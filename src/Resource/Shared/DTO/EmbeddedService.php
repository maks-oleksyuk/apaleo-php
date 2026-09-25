<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class EmbeddedService
{
    public function __construct(
        public string $id,
        public ?string $code,
        public ?string $name,
        public ?string $description,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::nullableString($data, 'code'),
            name: ResponseData::nullableString($data, 'name'),
            description: ResponseData::nullableString($data, 'description'),
        );
    }
}
