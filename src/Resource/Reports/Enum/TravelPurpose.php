<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\Enum;

enum TravelPurpose: string
{
    case Business = 'Business';
    case Leisure = 'Leisure';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
