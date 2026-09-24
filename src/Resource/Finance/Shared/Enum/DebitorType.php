<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Shared\Enum;

enum DebitorType: string
{
    case Booker = 'Booker';
    case PrimaryGuest = 'PrimaryGuest';
    case Company = 'Company';
    case AdditionalGuest = 'AdditionalGuest';
    case Property = 'Property';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
