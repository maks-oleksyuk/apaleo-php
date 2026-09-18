<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Inventory\Unit\Enum;

enum UnitMaintenanceType: string
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
