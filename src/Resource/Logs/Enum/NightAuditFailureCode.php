<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\Enum;

enum NightAuditFailureCode: string
{
    case None = 'None';
    case ProcessNoShowsFailed = 'ProcessNoShowsFailed';
    case ProcessFoliosFailed = 'ProcessFoliosFailed';
    case ProcessOccupiedUnitsFailed = 'ProcessOccupiedUnitsFailed';
    case Unknown = 'Unknown';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
