<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Account;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
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

final readonly class AccountResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @throws ApaleoExceptionInterface
     */
    public function getCurrent(): Account
    {
        $data = $this->pipeline->send(new GetCurrentAccountRequest());

        return Account::fromArray($data);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function replaceCurrent(ReplaceAccount $account): void
    {
        $this->pipeline->send(new ReplaceCurrentAccountRequest($account));
    }

    /**
     * @param list<string> $accountCodes
     *
     * @return PaginatedResult<AccountListItem>
     *
     * @throws ApaleoExceptionInterface
     */
    public function list(array $accountCodes = []): PaginatedResult
    {
        $data = $this->pipeline->send(new ListAccountsRequest($accountCodes));

        return PaginatedResult::fromResponse($data, 'accounts', AccountListItem::fromArray(...));
    }

    /**
     * @return string the code of the created account
     *
     * @throws ApaleoExceptionInterface
     */
    public function create(CreateAccount $account, ?string $idempotencyKey = null): string
    {
        $response = $this->pipeline->send(new CreateAccountRequest($account, $idempotencyKey));

        return ResponseData::string($response, 'code');
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function suspendCurrent(): void
    {
        $this->pipeline->send(new SuspendCurrentAccountRequest());
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function setCurrentLive(): void
    {
        $this->pipeline->send(new SetCurrentAccountLiveRequest());
    }
}
