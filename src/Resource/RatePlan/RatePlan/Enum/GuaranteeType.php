<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum;

enum GuaranteeType: string
{
    case PM6Hold = 'PM6Hold';
    case CreditCard = 'CreditCard';
    case Prepayment = 'Prepayment';
    case Company = 'Company';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
