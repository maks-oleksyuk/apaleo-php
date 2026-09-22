<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\Enum;

enum Title: string
{
    case Mr = 'Mr';
    case Ms = 'Ms';
    case Dr = 'Dr';
    case Prof = 'Prof';
    case Mrs = 'Mrs';
    case Other = 'Other';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
