<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum;

enum InvoiceRecipientType: string
{
    case Person = 'Person';
    case Company = 'Company';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
