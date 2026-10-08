<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Shared\Enum;

enum UnitCondition: string
{
    case Clean = 'Clean';
    case CleanToBeInspected = 'CleanToBeInspected';
    case Dirty = 'Dirty';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
