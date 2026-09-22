<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum;

enum AuthorizationTargetType: string
{
    case Booking = 'Booking';
    case Reservation = 'Reservation';
}
