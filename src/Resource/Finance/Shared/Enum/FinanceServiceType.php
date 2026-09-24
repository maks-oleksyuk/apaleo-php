<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Shared\Enum;

/** Wider than the Rate Plan API's ServiceType: finance also books fees and taxes. Create endpoints accept only Other, Accommodation, FoodAndBeverages, CityTax and SecondCityTax. */
enum FinanceServiceType: string
{
    case Other = 'Other';
    case Accommodation = 'Accommodation';
    case FoodAndBeverages = 'FoodAndBeverages';
    case CancellationFees = 'CancellationFees';
    case NoShow = 'NoShow';
    case CityTax = 'CityTax';
    case SecondCityTax = 'SecondCityTax';
    case LocalTax = 'LocalTax';
    case ConsumptionTax = 'ConsumptionTax';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
