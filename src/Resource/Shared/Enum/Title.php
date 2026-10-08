<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Shared\Enum;

enum Title: string
{
    case Mr = 'Mr';
    case Ms = 'Ms';
    case Dr = 'Dr';
    case Prof = 'Prof';
    case Mrs = 'Mrs';
    case Other = 'Other';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
