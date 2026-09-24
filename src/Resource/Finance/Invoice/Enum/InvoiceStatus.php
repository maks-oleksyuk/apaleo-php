<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum;

enum InvoiceStatus: string
{
    case FullyPaid = 'FullyPaid';
    case Unpaid = 'Unpaid';
    case WrittenOff = 'WrittenOff';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
