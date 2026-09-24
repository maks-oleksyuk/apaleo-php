<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class AgeCategoryListItem
{
    public function __construct(
        public string $id,
        public string $code,
        public string $propertyId,
        public string $name,
        public int $minAge,
        public int $maxAge,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            propertyId: ResponseData::string($data, 'propertyId'),
            name: ResponseData::string($data, 'name'),
            minAge: ResponseData::int($data, 'minAge'),
            maxAge: ResponseData::int($data, 'maxAge'),
        );
    }
}
