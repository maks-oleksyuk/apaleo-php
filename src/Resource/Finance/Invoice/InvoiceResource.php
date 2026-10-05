<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO\Invoice;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO\InvoiceListItem;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO\InvoicePreview;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceCancellationReason;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Requests\CancelInvoiceRequest;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Requests\CreateInvoiceRequest;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Requests\GetInvoicePdfRequest;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Requests\GetInvoiceRequest;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Requests\ListInvoicesRequest;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Requests\PayInvoiceRequest;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Requests\PreviewInvoicePdfRequest;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Requests\PreviewInvoiceRequest;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentMethod;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class InvoiceResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<'company'> $expand
     *
     * @throws ApaleoExceptionInterface
     */
    public function get(string $invoiceId, array $expand = []): Invoice
    {
        $data = $this->pipeline->send(new GetInvoiceRequest($invoiceId, $expand));

        return Invoice::fromArray($data);
    }

    /**
     * @return string the raw PDF bytes
     *
     * @throws ApaleoExceptionInterface
     */
    public function pdf(string $invoiceId): string
    {
        return $this->pipeline->sendRaw(new GetInvoicePdfRequest($invoiceId));
    }

    /**
     * @param list<'allowedActions'|'company'> $expand
     *
     * @return PaginatedResult<InvoiceListItem>
     *
     * @throws ApaleoExceptionInterface
     */
    public function list(InvoiceFilter $filter = new InvoiceFilter(), ?int $pageNumber = null, ?int $pageSize = null, array $expand = []): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListInvoicesRequest($filter, $pageNumber, $pageSize, $expand));

        return PaginatedResult::fromResponse($data, 'invoices', InvoiceListItem::fromArray(...));
    }

    /**
     * @param list<'company'> $expand
     *
     * @throws ApaleoExceptionInterface
     */
    public function preview(string $folioId, array $expand = []): InvoicePreview
    {
        $data = $this->pipeline->send(new PreviewInvoiceRequest($folioId, $expand));

        return InvoicePreview::fromArray($data);
    }

    /**
     * @return string the raw PDF bytes
     *
     * @throws ApaleoExceptionInterface
     */
    public function previewPdf(string $folioId, string $languageCode): string
    {
        return $this->pipeline->sendRaw(new PreviewInvoicePdfRequest($folioId, $languageCode));
    }

    /**
     * Check preview() first: it tells whether this would fail or also close the folio.
     *
     * @throws ApaleoExceptionInterface
     */
    public function create(string $folioId, string $languageCode, ?string $idempotencyKey = null): string
    {
        $data = $this->pipeline->send(new CreateInvoiceRequest($folioId, $languageCode, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    /**
     * Marks an invoice checked out on accounts receivable as paid.
     *
     * @throws ApaleoExceptionInterface
     */
    public function markAsPaid(string $invoiceId, PaymentMethod $paymentMethod, string $receipt): void
    {
        $this->pipeline->send(new PayInvoiceRequest($invoiceId, $paymentMethod, $receipt));
    }

    /**
     * Issues a cancellation invoice for it.
     *
     * @throws ApaleoExceptionInterface
     */
    public function cancel(string $invoiceId, InvoiceCancellationReason $reason): void
    {
        $this->pipeline->send(new CancelInvoiceRequest($invoiceId, $reason));
    }
}
