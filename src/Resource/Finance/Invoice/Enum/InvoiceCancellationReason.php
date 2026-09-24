<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum;

enum InvoiceCancellationReason: string
{
    case ChangeOfRecipientDetails = 'ChangeOfRecipientDetails';
    case ChangeOfInvoiceRecipient = 'ChangeOfInvoiceRecipient';
    case ChangeOfPaymentMethod = 'ChangeOfPaymentMethod';
    case ChangeOfInvoiceTransactions = 'ChangeOfInvoiceTransactions';
    case Other = 'Other';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
