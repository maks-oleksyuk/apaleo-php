<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum;

enum InvoiceType: string
{
    case Initial = 'Initial';
    case Cancellation = 'Cancellation';
    case Correction = 'Correction';
    case Advance = 'Advance';
    case AdvanceCancellation = 'AdvanceCancellation';
    case AdvanceCorrection = 'AdvanceCorrection';
    case Proforma = 'Proforma';
    case Deposit = 'Deposit';
    case DepositCancellation = 'DepositCancellation';
    case DepositCorrection = 'DepositCorrection';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
