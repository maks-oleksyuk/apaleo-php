<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedUnit;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class AutoAssignedUnitItem
{
    public function __construct(
        public EmbeddedUnit $unit,
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            unit: ResponseData::requiredNested($data, 'unit', EmbeddedUnit::fromArray(...)),
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
        );
    }
}
