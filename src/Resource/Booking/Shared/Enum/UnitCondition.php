<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\Enum;

/** Input only (assignUnit()'s unitConditions), so no Unknown fallback is needed. */
enum UnitCondition: string
{
    case Clean = 'Clean';
    case CleanToBeInspected = 'CleanToBeInspected';
    case Dirty = 'Dirty';
}
