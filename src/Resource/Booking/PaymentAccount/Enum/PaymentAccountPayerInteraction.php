<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Enum;

enum PaymentAccountPayerInteraction: string
{
    case PciToken = 'PciToken';
    case Terminal = 'Terminal';
    case Authorization = 'Authorization';
    case PaymentLink = 'PaymentLink';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
