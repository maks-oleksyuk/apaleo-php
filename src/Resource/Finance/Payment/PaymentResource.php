<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Payment;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\Split;
use Oleksyuk\Apaleo\Resource\Finance\Folio\DTO\SplitPaymentResult;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreateAccountPayment;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreateAuthorizationPayment;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreateCustomPayment;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreatePaymentLink;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\CreateTerminalPayment;
use Oleksyuk\Apaleo\Resource\Finance\Payment\DTO\Payment;
use Oleksyuk\Apaleo\Resource\Finance\Payment\Requests\CancelPaymentRequest;
use Oleksyuk\Apaleo\Resource\Finance\Payment\Requests\CreatePaymentRequest;
use Oleksyuk\Apaleo\Resource\Finance\Payment\Requests\GetPaymentRequest;
use Oleksyuk\Apaleo\Resource\Finance\Payment\Requests\ListPaymentsRequest;
use Oleksyuk\Apaleo\Resource\Finance\Payment\Requests\SplitPaymentRequest;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentStatus;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Payments on a folio. Terminal, payment-account and link payments start out Pending and settle asynchronously. */
final readonly class PaymentResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @param list<'actions'> $expand */
    public function get(string $folioId, string $paymentId, array $expand = []): Payment
    {
        $data = $this->pipeline->send(new GetPaymentRequest($folioId, $paymentId, $expand));

        return Payment::fromArray($data);
    }

    /**
     * @param list<PaymentStatus> $statuses
     * @param list<'actions'>     $expand
     *
     * @return PaginatedResult<Payment>
     */
    public function list(string $folioId, array $statuses = [], ?int $pageNumber = null, ?int $pageSize = null, array $expand = []): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListPaymentsRequest($folioId, $statuses, $pageNumber, $pageSize, $expand));

        return new PaginatedResult(
            items: array_map(Payment::fromArray(...), ResponseData::nestedList($data, 'payments')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function create(
        string $folioId,
        CreateAccountPayment|CreateAuthorizationPayment|CreateCustomPayment|CreatePaymentLink|CreateTerminalPayment $payment,
        ?string $idempotencyKey = null,
    ): string {
        $data = $this->pipeline->send(new CreatePaymentRequest($folioId, $payment, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    /** Only for a pending payment link. */
    public function cancel(string $folioId, string $paymentId): void
    {
        $this->pipeline->send(new CancelPaymentRequest($folioId, $paymentId));
    }

    public function split(string $folioId, string $paymentId, Split $split, ?string $idempotencyKey = null): SplitPaymentResult
    {
        $data = $this->pipeline->send(new SplitPaymentRequest($folioId, $paymentId, $split, $idempotencyKey));

        return SplitPaymentResult::fromArray($data);
    }
}
