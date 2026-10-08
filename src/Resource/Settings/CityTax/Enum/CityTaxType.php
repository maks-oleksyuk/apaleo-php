<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum;

enum CityTaxType: string
{
    case PercentOfGross = 'PercentOfGross';
    case PercentOfNet = 'PercentOfNet';
    case PerRoomPerNight = 'PerRoomPerNight';
    case PerPersonPerNight = 'PerPersonPerNight';
    case PerPersonPerNightBasedOnNetPrice = 'PerPersonPerNightBasedOnNetPrice';
    case PerPersonPerNightBasedOnGrossPrice = 'PerPersonPerNightBasedOnGrossPrice';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
