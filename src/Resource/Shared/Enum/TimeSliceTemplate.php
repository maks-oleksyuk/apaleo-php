<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Shared\Enum;

enum TimeSliceTemplate: string
{
    case DayUse = 'DayUse';
    case OverNight = 'OverNight';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
