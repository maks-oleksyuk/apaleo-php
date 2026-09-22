<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class OrderedServiceReservation
{
    public function __construct(
        public string $id,
        public \DateTimeImmutable $arrival,
        public \DateTimeImmutable $departure,
        public int $persons,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            arrival: ResponseData::dateTime($data, 'arrival'),
            departure: ResponseData::dateTime($data, 'departure'),
            persons: ResponseData::int($data, 'persons'),
        );
    }
}
