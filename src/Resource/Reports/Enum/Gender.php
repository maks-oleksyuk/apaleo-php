<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\Enum;

enum Gender: string
{
    case Female = 'Female';
    case Male = 'Male';
    case Other = 'Other';

    /** A real apaleo API value, distinct from the UnmappedValue fallback below. */
    case Unknown = 'Unknown';

    /** Fallback for a value apaleo added after this enum was written. */
    case UnmappedValue = '__unmapped__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::UnmappedValue;
    }
}
