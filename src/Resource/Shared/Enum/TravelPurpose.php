<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Shared\Enum;

enum TravelPurpose: string
{
    case Business = 'Business';
    case Leisure = 'Leisure';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
