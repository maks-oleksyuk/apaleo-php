<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\AgeCategory\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class AgeCategory
{
    /** @param array<string, string> $name localized, or ['default' => ...] when the endpoint returns a plain string */
    public function __construct(
        public string $id,
        public string $code,
        public string $propertyId,
        public array $name,
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
            name: ResponseData::localizedText($data, 'name'),
            minAge: ResponseData::int($data, 'minAge'),
            maxAge: ResponseData::int($data, 'maxAge'),
        );
    }
}
