<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Shared\Enum;

/** Invoice and the Psp* methods only ever come back from apaleo; they can't be sent when creating a payment or refund. */
enum PaymentMethod: string
{
    case Cash = 'Cash';
    case BankTransfer = 'BankTransfer';
    case CreditCard = 'CreditCard';
    case Invoice = 'Invoice';
    case Amex = 'Amex';
    case VisaCredit = 'VisaCredit';
    case VisaDebit = 'VisaDebit';
    case MasterCard = 'MasterCard';
    case MasterCardDebit = 'MasterCardDebit';
    case Maestro = 'Maestro';
    case GiroCard = 'GiroCard';
    case DiscoverCard = 'DiscoverCard';
    case Diners = 'Diners';
    case Jcb = 'Jcb';
    case BookingCom = 'BookingCom';
    case VPay = 'VPay';
    case PayPal = 'PayPal';
    case Postcard = 'Postcard';
    case Reka = 'Reka';
    case Twint = 'Twint';
    case Lunchcheck = 'Lunchcheck';
    case Voucher = 'Voucher';
    case ChinaUnionPay = 'ChinaUnionPay';
    case Other = 'Other';
    case Cheque = 'Cheque';
    case Airbnb = 'Airbnb';
    case HolidayCheck = 'HolidayCheck';
    case PspCash = 'PspCash';
    case PspDebit = 'PspDebit';
    case PspBanking = 'PspBanking';
    case PspOpenInvoice = 'PspOpenInvoice';
    case PspWallet = 'PspWallet';
    case Representation = 'Representation';
    case IDeal = 'IDeal';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
