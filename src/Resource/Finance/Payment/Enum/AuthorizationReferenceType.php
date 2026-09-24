<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Payment\Enum;

enum AuthorizationReferenceType: string
{
    case PspReference = 'PspReference';
    case AuthorizationId = 'AuthorizationId';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
