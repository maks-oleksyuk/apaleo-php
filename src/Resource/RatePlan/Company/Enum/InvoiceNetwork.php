<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Company\Enum;

enum InvoiceNetwork: string
{
    case Peppol = 'Peppol';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
