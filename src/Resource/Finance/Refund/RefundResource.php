<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Refund;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Finance\Refund\DTO\CreateFolioRefund;
use Oleksyuk\Apaleo\Resource\Finance\Refund\DTO\CreatePaymentRefund;
use Oleksyuk\Apaleo\Resource\Finance\Refund\DTO\Refund;
use Oleksyuk\Apaleo\Resource\Finance\Refund\Requests\CreateFolioRefundRequest;
use Oleksyuk\Apaleo\Resource\Finance\Refund\Requests\GetRefundRequest;
use Oleksyuk\Apaleo\Resource\Finance\Refund\Requests\ListRefundsRequest;
use Oleksyuk\Apaleo\Resource\Finance\Refund\Requests\RefundPaymentRequest;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\PaymentStatus;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class RefundResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function get(string $folioId, string $refundId): Refund
    {
        $data = $this->pipeline->send(new GetRefundRequest($folioId, $refundId));

        return Refund::fromArray($data);
    }

    /**
     * @param list<PaymentStatus> $statuses
     *
     * @return PaginatedResult<Refund>
     */
    public function list(string $folioId, array $statuses = [], ?int $pageNumber = null, ?int $pageSize = null): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListRefundsRequest($folioId, $statuses, $pageNumber, $pageSize));

        return new PaginatedResult(
            items: array_map(Refund::fromArray(...), ResponseData::nestedList($data, 'refunds')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function create(string $folioId, CreateFolioRefund $refund, ?string $idempotencyKey = null): string
    {
        $data = $this->pipeline->send(new CreateFolioRefundRequest($folioId, $refund, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    public function refundPayment(string $folioId, string $paymentId, CreatePaymentRefund $refund, ?string $idempotencyKey = null): string
    {
        $data = $this->pipeline->send(new RefundPaymentRequest($folioId, $paymentId, $refund, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }
}
