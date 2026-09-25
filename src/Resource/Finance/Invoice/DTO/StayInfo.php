<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class StayInfo
{
    public function __construct(
        public string $reservationId,
        public string $guestName,
        public \DateTimeImmutable $arrivalDate,
        public \DateTimeImmutable $departureDate,
        public ?string $roomNumber,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            reservationId: ResponseData::string($data, 'reservationId'),
            guestName: ResponseData::string($data, 'guestName'),
            arrivalDate: ResponseData::date($data, 'arrivalDate'),
            departureDate: ResponseData::date($data, 'departureDate'),
            roomNumber: ResponseData::nullableString($data, 'roomNumber'),
        );
    }
}
