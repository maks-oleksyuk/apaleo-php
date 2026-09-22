<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Service\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ServiceAvailabilityTimeSlice
{
    /** @param list<ServiceAvailabilityItem> $services */
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public array $services,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            services: array_map(ServiceAvailabilityItem::fromArray(...), ResponseData::nestedList($data, 'services')),
        );
    }
}
