<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\Enum;

/** Input only (a filter/offer parameter), so no Unknown fallback is needed. */
enum TimeSliceTemplate: string
{
    case DayUse = 'DayUse';
    case OverNight = 'OverNight';
}
