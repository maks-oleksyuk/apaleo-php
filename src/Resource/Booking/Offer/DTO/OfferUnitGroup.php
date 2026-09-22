<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class OfferUnitGroup
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public string $description,
        public int $maxPersons,
        public ?int $rank,
        public UnitGroupType $type,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::string($data, 'description'),
            maxPersons: ResponseData::int($data, 'maxPersons'),
            rank: ResponseData::nullableInt($data, 'rank'),
            type: UnitGroupType::fromApi(ResponseData::string($data, 'type')),
        );
    }
}
