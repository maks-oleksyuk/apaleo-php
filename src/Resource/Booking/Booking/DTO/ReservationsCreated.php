<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ReservationsCreated
{
    /** @param list<string> $reservationIds */
    public function __construct(
        public array $reservationIds,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            reservationIds: array_map(
                static fn (array $item): string => ResponseData::string($item, 'id'),
                ResponseData::nestedList($data, 'reservationIds'),
            ),
        );
    }
}
