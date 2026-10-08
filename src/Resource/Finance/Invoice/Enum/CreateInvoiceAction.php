<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum;

enum CreateInvoiceAction: string
{
    case CannotCreateInvoice = 'CannotCreateInvoice';
    case CreatesInvoice = 'CreatesInvoice';
    case CreatesInvoiceAndClosesFolio = 'CreatesInvoiceAndClosesFolio';
    case CreatesArInvoiceAndClosesFolio = 'CreatesArInvoiceAndClosesFolio';
    case Unrecognized = '__unrecognized__';

    public static function fromApi(string $value): self
    {
        return self::tryFrom($value) ?? self::Unrecognized;
    }
}
