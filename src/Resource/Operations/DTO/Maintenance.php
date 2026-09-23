<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Operations\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedUnit;
use Oleksyuk\Apaleo\Resource\Operations\Enum\MaintenanceType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Maintenance
{
    public function __construct(
        public string $id,
        public EmbeddedUnit $unit,
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public MaintenanceType $type,
        public ?string $description,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            unit: EmbeddedUnit::fromArray(ResponseData::nested($data, 'unit')),
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            type: MaintenanceType::fromApi(ResponseData::string($data, 'type')),
            description: ResponseData::nullableString($data, 'description'),
        );
    }
}
