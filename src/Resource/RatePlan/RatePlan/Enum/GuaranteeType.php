<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum;

enum GuaranteeType: string
{
    case PM6Hold = 'PM6Hold';
    case CreditCard = 'CreditCard';
    case Prepayment = 'Prepayment';
    case Company = 'Company';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
