<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\PaymentAccount;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO\PaymentAccount;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO\PaymentAccountDetails;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\DTO\PaymentAccountTarget;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Requests\CreatePaymentAccountByAuthorizationRequest;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Requests\CreatePaymentAccountByLinkRequest;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Requests\CreatePaymentAccountByStoredPaymentMethodRequest;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Requests\CreatePaymentAccountByTerminalRequest;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Requests\GetPaymentAccountRequest;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Requests\ListPaymentAccountsRequest;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\Requests\PaymentAccountSimpleActionRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class PaymentAccountResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @param list<'actions'> $expand */
    public function get(string $paymentAccountId, array $expand = []): PaymentAccount
    {
        $data = $this->pipeline->send(new GetPaymentAccountRequest($paymentAccountId, $expand));

        return PaymentAccount::fromArray($data);
    }

    /**
     * @param list<string> $sort
     * @param list<'actions'> $expand
     *
     * @return PaginatedResult<PaymentAccount>
     */
    public function list(
        PaymentAccountFilter $filter = new PaymentAccountFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $sort = [],
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListPaymentAccountsRequest($filter, $pageNumber, $pageSize, $sort, $expand));

        return new PaginatedResult(
            items: array_map(PaymentAccount::fromArray(...), ResponseData::nestedList($data, 'paymentAccounts')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function createByAuthorization(PaymentAccountTarget $target, string $transactionReference, ?string $idempotencyKey = null): string
    {
        $data = $this->pipeline->send(new CreatePaymentAccountByAuthorizationRequest($target, $transactionReference, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    /** Creates a hosted payment-link payment account; see the returned PaymentAccount::$paymentLink once fetched. */
    public function createByLink(
        PaymentAccountTarget $target,
        string $propertyId,
        string $countryCode,
        \DateTimeImmutable $expiresAt,
        ?string $description = null,
        ?string $payerEmail = null,
        ?string $returnUrl = null,
        ?string $idempotencyKey = null,
    ): string {
        $data = $this->pipeline->send(new CreatePaymentAccountByLinkRequest($target, $propertyId, $countryCode, $expiresAt, $description, $payerEmail, $returnUrl, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    /** @param string $storedPaymentMethodId a specific stored method's id, or 'LATEST' for the payer's most recent one */
    public function createByStoredPaymentMethod(
        PaymentAccountTarget $target,
        string $payerReference,
        string $storedPaymentMethodId,
        ?bool $isVirtual = null,
        ?PaymentAccountDetails $accountDetails = null,
        ?string $idempotencyKey = null,
    ): string {
        $data = $this->pipeline->send(new CreatePaymentAccountByStoredPaymentMethodRequest($target, $payerReference, $storedPaymentMethodId, $isVirtual, $accountDetails, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    public function createByTerminal(PaymentAccountTarget $target, string $propertyId, string $terminalId, ?string $idempotencyKey = null): string
    {
        $data = $this->pipeline->send(new CreatePaymentAccountByTerminalRequest($target, $propertyId, $terminalId, $idempotencyKey));

        return ResponseData::string($data, 'id');
    }

    public function cancel(string $paymentAccountId): void
    {
        $this->pipeline->send(new PaymentAccountSimpleActionRequest($paymentAccountId, 'cancel'));
    }

    public function expirePaymentLink(string $paymentAccountId): void
    {
        $this->pipeline->send(new PaymentAccountSimpleActionRequest($paymentAccountId, 'expire-payment-link'));
    }
}
