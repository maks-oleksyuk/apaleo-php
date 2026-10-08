<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\Enum;

enum BlockStatus: string
{
    case Tentative = 'Tentative';
    case Definite = 'Definite';
    case Canceled = 'Canceled';
    case Optional = 'Optional';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
