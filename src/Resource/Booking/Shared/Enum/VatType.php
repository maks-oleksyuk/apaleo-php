<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Shared\Enum;

enum VatType: string
{
    case Null = 'Null';
    case VeryReduced = 'VeryReduced';
    case Reduced = 'Reduced';
    case Normal = 'Normal';
    case Without = 'Without';
    case Special = 'Special';
    case Special1 = 'Special1';
    case Special2 = 'Special2';
    case ReducedCovid19 = 'ReducedCovid19';
    case NormalCovid19 = 'NormalCovid19';
    case Mixed = 'Mixed';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
