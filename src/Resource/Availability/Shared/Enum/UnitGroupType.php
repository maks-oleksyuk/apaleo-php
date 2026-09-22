<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Shared\Enum;

enum UnitGroupType: string
{
    case BedRoom = 'BedRoom';
    case MeetingRoom = 'MeetingRoom';
    case EventSpace = 'EventSpace';
    case ParkingLot = 'ParkingLot';
    case Other = 'Other';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
