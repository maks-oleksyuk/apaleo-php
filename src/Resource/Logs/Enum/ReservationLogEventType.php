<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\Enum;

enum ReservationLogEventType: string
{
    case Created = 'Created';
    case Amended = 'Amended';
    case CheckedIn = 'CheckedIn';
    case CheckedOut = 'CheckedOut';
    case Canceled = 'Canceled';
    case SetToNoShow = 'SetToNoShow';
    case CityTaxAdded = 'CityTaxAdded';
    case CityTaxRemoved = 'CityTaxRemoved';
    case UnitAssigned = 'UnitAssigned';
    case UnitUnassigned = 'UnitUnassigned';
    case PaymentAccountSet = 'PaymentAccountSet';
    case PaymentAccountRemoved = 'PaymentAccountRemoved';
    case InvoiceStatusChanged = 'InvoiceStatusChanged';
    case Changed = 'Changed';
    case CheckInReverted = 'CheckInReverted';
    case UnitLocked = 'UnitLocked';
    case UnitUnlocked = 'UnitUnlocked';
    case PickedUpFromBlock = 'PickedUpFromBlock';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
