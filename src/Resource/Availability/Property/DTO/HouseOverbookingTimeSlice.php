<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Property\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class HouseOverbookingTimeSlice
{
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public int $houseOverbookingLimit,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            houseOverbookingLimit: ResponseData::int($data, 'houseOverbookingLimit'),
        );
    }
}
