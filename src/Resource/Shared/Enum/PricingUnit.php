<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Shared\Enum;

enum PricingUnit: string
{
    case Room = 'Room';
    case Person = 'Person';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
