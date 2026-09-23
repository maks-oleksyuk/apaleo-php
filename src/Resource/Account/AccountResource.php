<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Account;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Account\DTO\Account;
use Oleksyuk\Apaleo\Resource\Account\DTO\AccountListItem;
use Oleksyuk\Apaleo\Resource\Account\DTO\CreateAccount;
use Oleksyuk\Apaleo\Resource\Account\DTO\ReplaceAccount;
use Oleksyuk\Apaleo\Resource\Account\Requests\CreateAccountRequest;
use Oleksyuk\Apaleo\Resource\Account\Requests\GetCurrentAccountRequest;
use Oleksyuk\Apaleo\Resource\Account\Requests\ListAccountsRequest;
use Oleksyuk\Apaleo\Resource\Account\Requests\ReplaceCurrentAccountRequest;
use Oleksyuk\Apaleo\Resource\Account\Requests\SetCurrentAccountLiveRequest;
use Oleksyuk\Apaleo\Resource\Account\Requests\SuspendCurrentAccountRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The Account API (account-v1) — the current account plus account administration, so no sub-resources beneath this one. */
final readonly class AccountResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function getCurrent(): Account
    {
        $data = $this->pipeline->send(new GetCurrentAccountRequest());

        return Account::fromArray($data);
    }

    public function replaceCurrent(ReplaceAccount $account): void
    {
        $this->pipeline->send(new ReplaceCurrentAccountRequest($account));
    }

    /**
     * @param list<string> $accountCodes
     *
     * @return PaginatedResult<AccountListItem>
     */
    public function list(array $accountCodes = []): PaginatedResult
    {
        $data = $this->pipeline->send(new ListAccountsRequest($accountCodes));

        return new PaginatedResult(
            items: array_map(AccountListItem::fromArray(...), ResponseData::nestedList($data, 'accounts')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    /** @return string the code of the created account */
    public function create(CreateAccount $account, ?string $idempotencyKey = null): string
    {
        $response = $this->pipeline->send(new CreateAccountRequest($account, $idempotencyKey));

        return ResponseData::string($response, 'code');
    }

    public function suspendCurrent(): void
    {
        $this->pipeline->send(new SuspendCurrentAccountRequest());
    }

    public function setCurrentLive(): void
    {
        $this->pipeline->send(new SetCurrentAccountLiveRequest());
    }
}
