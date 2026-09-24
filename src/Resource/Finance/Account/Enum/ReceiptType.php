<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\Enum;

enum ReceiptType: string
{
    case Custom = 'Custom';
    case Reservation = 'Reservation';
    case Invoice = 'Invoice';
    case PspReference = 'PspReference';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
