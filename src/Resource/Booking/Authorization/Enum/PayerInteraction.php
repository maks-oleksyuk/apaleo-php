<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum;

enum PayerInteraction: string
{
    case Terminal = 'Terminal';
    case PaymentAccount = 'PaymentAccount';
    case Authorization = 'Authorization';
    case PaymentLink = 'PaymentLink';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
