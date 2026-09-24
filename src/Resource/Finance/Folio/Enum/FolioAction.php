<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Enum;

enum FolioAction: string
{
    case AddCharge = 'AddCharge';
    case AddDepositItem = 'AddDepositItem';
    case AddAllowance = 'AddAllowance';
    case AddCancellationFee = 'AddCancellationFee';
    case AddNoShowFee = 'AddNoShowFee';
    case AddPayment = 'AddPayment';
    case AddRefund = 'AddRefund';
    case CheckoutOnAr = 'CheckoutOnAr';
    case Close = 'Close';
    case PostOpenCharges = 'PostOpenCharges';
    case CorrectFolio = 'CorrectFolio';
    case ChangeAddress = 'ChangeAddress';
    case ChangeAddressWithSimpleDebitor = 'ChangeAddressWithSimpleDebitor';
    case Delete = 'Delete';
    case Reopen = 'Reopen';
    case CreateInvoice = 'CreateInvoice';
    case CreateAdvanceInvoice = 'CreateAdvanceInvoice';
    case CancelLastInvoice = 'CancelLastInvoice';
    case CreateInvoiceWithSimpleDebitor = 'CreateInvoiceWithSimpleDebitor';
    case CreatePrepaymentNotice = 'CreatePrepaymentNotice';
    case CreateProFormaInvoice = 'CreateProFormaInvoice';
    case CreateDepositReceipt = 'CreateDepositReceipt';
    case PreviewInvoice = 'PreviewInvoice';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
