<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum;

enum CreateInvoiceWarningType: string
{
    case InvoiceAlreadyExists = 'InvoiceAlreadyExists';
    case NotAllChargesPosted = 'NotAllChargesPosted';
    case DebitorDetailsMissing = 'DebitorDetailsMissing';
    case InvoiceHasPendingPayments = 'InvoiceHasPendingPayments';
    case NoCompanyFound = 'NoCompanyFound';
    case CompanyCannotCheckOutOnAr = 'CompanyCannotCheckOutOnAr';
    case IsHouseFolio = 'IsHouseFolio';
    case CannotCreateCompanyInvoiceForExternal = 'CannotCreateCompanyInvoiceForExternal';
    case CheckOutOnArIsNotAllowed = 'CheckOutOnArIsNotAllowed';
    case IsEmptyFolio = 'IsEmptyFolio';
    case CashPaymentLimitExceeded = 'CashPaymentLimitExceeded';
    case FolioState = 'FolioState';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
