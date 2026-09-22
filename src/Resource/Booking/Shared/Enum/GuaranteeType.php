<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\Enum;

enum GuaranteeType: string
{
    case PM6Hold = 'PM6Hold';
    case CreditCard = 'CreditCard';
    case Prepayment = 'Prepayment';
    case Company = 'Company';

    /** only ever returned on read; not accepted when creating/amending a reservation */
    case Ota = 'Ota';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
