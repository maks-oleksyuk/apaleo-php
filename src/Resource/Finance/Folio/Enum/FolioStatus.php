<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Enum;

enum FolioStatus: string
{
    case Open = 'Open';
    case Closed = 'Closed';
    case ClosedWithInvoice = 'ClosedWithInvoice';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
