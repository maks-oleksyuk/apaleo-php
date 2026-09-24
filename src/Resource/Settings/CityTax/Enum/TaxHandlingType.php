<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\CityTax\Enum;

enum TaxHandlingType: string
{
    case BeforeTax = 'BeforeTax';
    case AfterTax = 'AfterTax';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
