<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\Property\Enum\PropertyStatus;
use Oleksyuk\Apaleo\Support\ResponseData;

final class Property
{
    /**
     * @param array<string, string> $name localized name, keyed by language code
     */
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly array $name,
        public readonly string $timeZone,
        public readonly string $currencyCode,
        public readonly PropertyStatus $status,
        public readonly string $rawStatus,
        public readonly bool $isArchived,
        public readonly \DateTimeImmutable $created,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $status = ResponseData::string($data, 'status');
        /** @var array<string, string> $name */
        $name = is_array($data['name'] ?? null) ? $data['name'] : [];

        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: $name,
            timeZone: ResponseData::string($data, 'timeZone'),
            currencyCode: ResponseData::string($data, 'currencyCode'),
            status: PropertyStatus::fromApi($status),
            rawStatus: $status,
            isArchived: ResponseData::bool($data, 'isArchived'),
            created: new \DateTimeImmutable(ResponseData::string($data, 'created')),
        );
    }
}
