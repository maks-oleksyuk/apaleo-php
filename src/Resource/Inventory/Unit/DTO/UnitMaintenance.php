<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum\UnitMaintenanceType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class UnitMaintenance
{
    public function __construct(
        public string $id,
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public UnitMaintenanceType $type,
        public string $rawType,
        public ?string $description,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $type = ResponseData::string($data, 'type');

        return new self(
            id: ResponseData::string($data, 'id'),
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            type: UnitMaintenanceType::fromApi($type),
            rawType: $type,
            description: ResponseData::nullableString($data, 'description'),
        );
    }
}
