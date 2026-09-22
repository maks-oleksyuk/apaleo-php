<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Shared\Enum;

enum UnitCondition: string
{
    case Clean = 'Clean';
    case CleanToBeInspected = 'CleanToBeInspected';
    case Dirty = 'Dirty';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
