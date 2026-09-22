<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum;

enum DateFilter: string
{
    case Arrival = 'Arrival';
    case Departure = 'Departure';
    case Stay = 'Stay';
    case Creation = 'Creation';
    case Modification = 'Modification';
    case Cancellation = 'Cancellation';
    case ArrivalAndCheckIn = 'ArrivalAndCheckIn';
    case DepartureAndCheckOut = 'DepartureAndCheckOut';
}
