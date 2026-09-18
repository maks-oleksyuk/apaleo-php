<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Property\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\Property\Enum\PropertyStatus;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Property
{
    /**
     * @param array<string, string> $name localized name, keyed by language code
     */
    public function __construct(
        public string $id,
        public string $code,
        public array $name,
        public string $timeZone,
        public string $currencyCode,
        public PropertyStatus $status,
        public string $rawStatus,
        public bool $isArchived,
        public \DateTimeImmutable $created,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $status = ResponseData::string($data, 'status');

        /** @var array<string, string> $name */
        $name = \is_array($data['name'] ?? null) ? $data['name'] : [];

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
