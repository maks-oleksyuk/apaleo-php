<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum;

enum ReservationStatus: string
{
    case Confirmed = 'Confirmed';
    case InHouse = 'InHouse';
    case CheckedOut = 'CheckedOut';
    case Canceled = 'Canceled';
    case NoShow = 'NoShow';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
