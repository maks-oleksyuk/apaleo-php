<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum;

enum RemittanceResponsibility: string
{
    case Hotel = 'Hotel';
    case Ota = 'Ota';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
