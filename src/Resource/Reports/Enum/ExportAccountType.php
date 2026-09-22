<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\Enum;

enum ExportAccountType: string
{
    case Revenues = 'Revenues';
    case Payments = 'Payments';
    case Liabilities = 'Liabilities';
    case Receivables = 'Receivables';
    case Vat = 'Vat';
    case House = 'House';
    case AccountsReceivable = 'AccountsReceivable';
    case CityTaxes = 'CityTaxes';
    case TransitoryItems = 'TransitoryItems';
    case VatOnLiabilities = 'VatOnLiabilities';
    case LossOfAccountsReceivable = 'LossOfAccountsReceivable';
    case SecondCityTax = 'SecondCityTax';
    case GuestLevies = 'GuestLevies';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
