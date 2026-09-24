<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceCancellationReason;

final readonly class CancelInvoiceRequest extends Request
{
    public function __construct(
        private string $invoiceId,
        private InvoiceCancellationReason $reason,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/finance/v1/invoice-actions/'.rawurlencode($this->invoiceId).'/cancel';
    }

    public function body(): array
    {
        return ['reasonCode' => $this->reason->value];
    }
}
