<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum;

enum AuthorizationTargetType: string
{
    case Booking = 'Booking';
    case Reservation = 'Reservation';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
