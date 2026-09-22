<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Shared\Enum;

enum MaintenanceType: string
{
    case OutOfService = 'OutOfService';
    case OutOfOrder = 'OutOfOrder';
    case OutOfInventory = 'OutOfInventory';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
