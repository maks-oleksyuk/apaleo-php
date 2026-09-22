<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Shared\Enum;

enum TimeSliceTemplate: string
{
    case DayUse = 'DayUse';
    case OverNight = 'OverNight';
}
