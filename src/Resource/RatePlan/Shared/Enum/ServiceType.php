<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum;

enum ServiceType: string
{
    case Other = 'Other';
    case Accommodation = 'Accommodation';
    case FoodAndBeverages = 'FoodAndBeverages';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
