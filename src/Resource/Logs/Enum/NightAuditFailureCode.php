<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\Enum;

enum NightAuditFailureCode: string
{
    case None = 'None';
    case ProcessNoShowsFailed = 'ProcessNoShowsFailed';
    case ProcessFoliosFailed = 'ProcessFoliosFailed';
    case ProcessOccupiedUnitsFailed = 'ProcessOccupiedUnitsFailed';

    /** A real apaleo API value, distinct from the UnmappedValue fallback below. */
    case Unknown = 'Unknown';

    /** Fallback for a value apaleo added after this enum was written. */
    case UnmappedValue = '__unmapped__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::UnmappedValue;
    }
}
