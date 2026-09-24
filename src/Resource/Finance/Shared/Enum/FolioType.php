<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Shared\Enum;

enum FolioType: string
{
    case House = 'House';
    case Guest = 'Guest';
    case External = 'External';
    case Booking = 'Booking';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
