<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Account\Enum;

enum AccountType: string
{
    case Trial = 'Trial';
    case Live = 'Live';
    case Suspended = 'Suspended';
    case Development = 'Development';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
