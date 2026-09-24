<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\Enum;

enum TransactionCommand: string
{
    case PostCharge = 'PostCharge';
    case PostPayment = 'PostPayment';
    case MoveLineItem = 'MoveLineItem';
    case PostPrepayment = 'PostPrepayment';
    case PostToAccountsReceivables = 'PostToAccountsReceivables';
    case PostPrepaymentVat = 'PostPrepaymentVat';
    case PostToLossOfAccountsReceivables = 'PostToLossOfAccountsReceivables';
    case System = 'System';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
