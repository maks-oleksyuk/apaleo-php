<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\Enum;

enum AccountingSchema: string
{
    case Simple = 'Simple';
    case Extended = 'Extended';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
