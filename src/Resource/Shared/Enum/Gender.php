<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Shared\Enum;

enum Gender: string
{
    case Female = 'Female';
    case Male = 'Male';
    case Other = 'Other';
    case Unknown = 'Unknown';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
