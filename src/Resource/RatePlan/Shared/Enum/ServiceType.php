<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Shared\Enum;

enum ServiceType: string
{
    case Other = 'Other';
    case Accommodation = 'Accommodation';
    case FoodAndBeverages = 'FoodAndBeverages';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
