<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final readonly class PreviewInvoicePdfRequest extends Request
{
    public function __construct(
        private string $folioId,
        private string $languageCode,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/finance/v1/invoices/preview-pdf';
    }

    public function accept(): string
    {
        return 'application/pdf';
    }

    public function query(): array
    {
        return ['folioId' => $this->folioId, 'languageCode' => $this->languageCode];
    }
}
