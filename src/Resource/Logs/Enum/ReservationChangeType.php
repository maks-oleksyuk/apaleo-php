<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\Enum;

enum ReservationChangeType: string
{
    case ReservationAdded = 'ReservationAdded';
    case ReservationChanged = 'ReservationChanged';
    case AdditionalGuestAdded = 'AdditionalGuestAdded';
    case AdditionalGuestRemoved = 'AdditionalGuestRemoved';
    case AdditionalGuestChanged = 'AdditionalGuestChanged';
    case TimeSliceAdded = 'TimeSliceAdded';
    case TimeSliceRemoved = 'TimeSliceRemoved';
    case TimeSliceChanged = 'TimeSliceChanged';
    case ExtraServiceAdded = 'ExtraServiceAdded';
    case ExtraServiceRemoved = 'ExtraServiceRemoved';
    case ExtraServiceChanged = 'ExtraServiceChanged';
    case ValidationMessageAdded = 'ValidationMessageAdded';
    case ValidationMessageRemoved = 'ValidationMessageRemoved';
    case UnitAssignmentLockChanged = 'UnitAssignmentLockChanged';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
