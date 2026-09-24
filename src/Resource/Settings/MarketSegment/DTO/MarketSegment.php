<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\MarketSegment\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** Shape of both the list and the single GET. */
final readonly class MarketSegment
{
    /** @param list<string> $propertyIds empty when the segment isn't restricted to specific properties */
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public ?string $description,
        public array $propertyIds,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::nullableString($data, 'description'),
            propertyIds: ResponseData::stringListOrEmpty($data, 'propertyIds'),
        );
    }
}
