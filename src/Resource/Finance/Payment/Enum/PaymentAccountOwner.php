<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Payment\Enum;

enum PaymentAccountOwner: string
{
    case Guest = 'Guest';
    case Booker = 'Booker';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
