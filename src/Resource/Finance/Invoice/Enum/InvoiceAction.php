<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum;

enum InvoiceAction: string
{
    case CorrectAddress = 'CorrectAddress';
    case CorrectCharges = 'CorrectCharges';
    case MarkAsPaid = 'MarkAsPaid';
    case Cancel = 'Cancel';
    case WriteOff = 'WriteOff';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
