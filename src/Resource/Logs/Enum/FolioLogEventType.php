<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\Enum;

enum FolioLogEventType: string
{
    case Created = 'Created';
    case ChargePosted = 'ChargePosted';
    case TransitoryChargePosted = 'TransitoryChargePosted';
    case AllowancePosted = 'AllowancePosted';
    case PaymentPosted = 'PaymentPosted';
    case ChargeMovedFromFolio = 'ChargeMovedFromFolio';
    case TransitoryChargeMovedFromFolio = 'TransitoryChargeMovedFromFolio';
    case PaymentMovedFromFolio = 'PaymentMovedFromFolio';
    case ChargeMovedToFolio = 'ChargeMovedToFolio';
    case TransitoryChargeMovedToFolio = 'TransitoryChargeMovedToFolio';
    case PaymentMovedToFolio = 'PaymentMovedToFolio';
    case Closed = 'Closed';
    case Reopened = 'Reopened';
    case Deleted = 'Deleted';
    case DebitorChanged = 'DebitorChanged';
    case AllowanceMovedFromFolio = 'AllowanceMovedFromFolio';
    case AllowanceMovedToFolio = 'AllowanceMovedToFolio';
    case RefundPosted = 'RefundPosted';
    case RefundMovedFromFolio = 'RefundMovedFromFolio';
    case RefundMovedToFolio = 'RefundMovedToFolio';
    case InvoiceCreated = 'InvoiceCreated';
    case InvoiceCanceled = 'InvoiceCanceled';
    case InvoicePaid = 'InvoicePaid';
    case ChargesChanged = 'ChargesChanged';
    case PaymentAdded = 'PaymentAdded';
    case PaymentFailed = 'PaymentFailed';
    case PaymentCanceled = 'PaymentCanceled';
    case RefundAdded = 'RefundAdded';
    case RefundFailed = 'RefundFailed';
    case InvoiceWrittenOff = 'InvoiceWrittenOff';
    case DepositItemAdded = 'DepositItemAdded';
    case DepositItemChanged = 'DepositItemChanged';
    case DepositItemDeleted = 'DepositItemDeleted';
    case InvoiceFailed = 'InvoiceFailed';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
