<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\Enum;

enum FolioWarning: string
{
    case IncompleteBillingAddress = 'IncompleteBillingAddress';
    case IncompleteBillingDetails = 'IncompleteBillingDetails';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
