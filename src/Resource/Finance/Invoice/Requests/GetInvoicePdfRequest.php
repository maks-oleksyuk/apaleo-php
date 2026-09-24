<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class GetInvoicePdfRequest extends Request
{
    public function __construct(
        private string $invoiceId,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/finance/v1/invoices/'.rawurlencode($this->invoiceId).'/pdf';
    }

    public function accept(): string
    {
        return 'application/pdf';
    }
}
