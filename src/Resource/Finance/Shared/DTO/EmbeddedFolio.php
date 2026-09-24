<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Shared\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class EmbeddedFolio
{
    /** @param ?string $debitor display name of whoever the folio is billed to */
    public function __construct(
        public string $id,
        public ?string $debitor,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            debitor: ResponseData::nullableString($data, 'debitor'),
        );
    }

    /** @param array<string, mixed> $data */
    public static function fromNested(array $data, string $key): ?self
    {
        $folio = ResponseData::nested($data, $key);

        return $folio !== [] ? self::fromArray($folio) : null;
    }
}
