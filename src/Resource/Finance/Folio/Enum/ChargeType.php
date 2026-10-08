<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Enum;

enum ChargeType: string
{
    case Direct = 'Direct';
    case TimeSlice = 'TimeSlice';
    case IncludedService = 'IncludedService';
    case ExtraService = 'ExtraService';
    case CityTax = 'CityTax';
    case NoShowFee = 'NoShowFee';
    case CancellationFee = 'CancellationFee';
    case ServiceFee = 'ServiceFee';
    case Tax = 'Tax';
    case SecondCityTax = 'SecondCityTax';
    case GuestLevy = 'GuestLevy';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
