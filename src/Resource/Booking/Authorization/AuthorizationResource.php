<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\DTO\Authorization;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\DTO\AuthorizationTarget;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests\AuthorizationSimpleActionRequest;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests\CreateAuthorizationByAuthorizationRequest;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests\CreateAuthorizationByLinkRequest;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests\CreateAuthorizationByPaymentAccountRequest;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests\CreateAuthorizationByTerminalRequest;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests\GetAuthorizationRequest;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests\ListAuthorizationsRequest;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Requests\RefreshAuthorizationRequest;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class AuthorizationResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @param list<'actions'> $expand */
    public function get(string $authorizationId, array $expand = []): Authorization
    {
        $data = $this->pipeline->send(new GetAuthorizationRequest($authorizationId, $expand));

        return Authorization::fromArray($data);
    }

    /**
     * @param list<string> $sort
     * @param list<'actions'|'remainingBalance'> $expand
     *
     * @return PaginatedResult<Authorization>
     */
    public function list(
        AuthorizationFilter $filter = new AuthorizationFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $sort = [],
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListAuthorizationsRequest($filter, $pageNumber, $pageSize, $sort, $expand));

        return new PaginatedResult(
            items: array_map(Authorization::fromArray(...), ResponseData::nestedList($data, 'authorizations')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function createByAuthorization(AuthorizationTarget $target, MonetaryValue $amount, string $transactionReference): string
    {
        $data = $this->pipeline->send(new CreateAuthorizationByAuthorizationRequest($target, $amount, $transactionReference));

        return ResponseData::string($data, 'id');
    }

    /** Creates a hosted payment-link authorization; see the returned Authorization::$paymentLinkUrl once fetched. */
    public function createByLink(
        AuthorizationTarget $target,
        MonetaryValue $amount,
        string $countryCode,
        \DateTimeImmutable $expiresAt,
        ?string $description = null,
        ?string $payerEmail = null,
        ?string $returnUrl = null,
    ): string {
        $data = $this->pipeline->send(new CreateAuthorizationByLinkRequest($target, $amount, $countryCode, $expiresAt, $description, $payerEmail, $returnUrl));

        return ResponseData::string($data, 'id');
    }

    public function createByPaymentAccount(AuthorizationTarget $target, MonetaryValue $amount, ?string $paymentAccountId = null): string
    {
        $data = $this->pipeline->send(new CreateAuthorizationByPaymentAccountRequest($target, $amount, $paymentAccountId));

        return ResponseData::string($data, 'id');
    }

    public function createByTerminal(AuthorizationTarget $target, MonetaryValue $amount, string $terminalId): string
    {
        $data = $this->pipeline->send(new CreateAuthorizationByTerminalRequest($target, $amount, $terminalId));

        return ResponseData::string($data, 'id');
    }

    public function cancel(string $authorizationId): void
    {
        $this->pipeline->send(new AuthorizationSimpleActionRequest($authorizationId, 'cancel'));
    }

    public function expirePaymentLink(string $authorizationId): void
    {
        $this->pipeline->send(new AuthorizationSimpleActionRequest($authorizationId, 'expire-payment-link'));
    }

    public function refresh(string $authorizationId, MonetaryValue $amount): void
    {
        $this->pipeline->send(new RefreshAuthorizationRequest($authorizationId, $amount));
    }
}
