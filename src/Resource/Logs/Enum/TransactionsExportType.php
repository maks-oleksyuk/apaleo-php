<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\Enum;

enum TransactionsExportType: string
{
    case Raw = 'Raw';
    case Aggregate = 'Aggregate';
    case AggregatePairs = 'AggregatePairs';
    case Unknown = '__unknown__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unknown;
    }
}
