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
    public static function fromNested(array $data, string $key): ?self
    {
        $stay = ResponseData::nested($data, $key);

        return $stay !== [] ? new self(
            reservationId: ResponseData::string($stay, 'reservationId'),
            guestName: ResponseData::string($stay, 'guestName'),
            arrivalDate: ResponseData::date($stay, 'arrivalDate'),
            departureDate: ResponseData::date($stay, 'departureDate'),
            roomNumber: ResponseData::nullableString($stay, 'roomNumber'),
        ) : null;
    }
}
