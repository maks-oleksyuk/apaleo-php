<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;

final readonly class PayInvoiceRequest extends Request
{
    public function __construct(
        private string $invoiceId,
        private PaymentMethod $paymentMethod,
        private string $receipt,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/finance/v1/invoice-actions/'.rawurlencode($this->invoiceId).'/pay';
    }

    public function body(): array
    {
        return ['paymentMethod' => $this->paymentMethod->value, 'receipt' => $this->receipt];
    }
}
