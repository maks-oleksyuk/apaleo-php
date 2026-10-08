<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum;

enum ReservationValidationMessageCategory: string
{
    case OfferNotAvailable = 'OfferNotAvailable';
    case AutoUnitAssignment = 'AutoUnitAssignment';
    case GuestLoyalty = 'GuestLoyalty';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
