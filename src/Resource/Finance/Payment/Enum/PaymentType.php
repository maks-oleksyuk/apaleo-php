<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Payment\Enum;

enum PaymentType: string
{
    case Custom = 'Custom';
    case Terminal = 'Terminal';
    case PaymentAccount = 'PaymentAccount';
    case Authorization = 'Authorization';
    case PaymentLink = 'PaymentLink';
    case PreAuthorization = 'PreAuthorization';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
