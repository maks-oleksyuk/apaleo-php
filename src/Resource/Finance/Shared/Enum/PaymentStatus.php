<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Shared\Enum;

enum PaymentStatus: string
{
    case Pending = 'Pending';
    case Success = 'Success';
    case Failure = 'Failure';
    case Canceled = 'Canceled';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
