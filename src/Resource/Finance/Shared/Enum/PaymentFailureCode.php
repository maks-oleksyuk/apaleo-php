<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Shared\Enum;

enum PaymentFailureCode: string
{
    case Failed = 'Failed';
    case TimedOut = 'TimedOut';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
