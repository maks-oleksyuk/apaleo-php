<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\SubAccount;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Exception\ApaleoNotFoundException;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\DTO\CreateSubAccount;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\DTO\SubAccount;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\Requests\CountSubAccountsRequest;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\Requests\CreateSubAccountRequest;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\Requests\DeleteSubAccountRequest;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\Requests\GetSubAccountRequest;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\Requests\ListSubAccountsRequest;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\Requests\SubAccountExistsRequest;
use Oleksyuk\Apaleo\Resource\Settings\SubAccount\Requests\UpdateSubAccountRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Custom revenue sub-accounts; FeatureSettings::$areCustomRevenueSubAccountsEnabled must be on to use them. */
final readonly class SubAccountResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @throws ApaleoExceptionInterface
     */
    public function get(string $subAccountId): SubAccount
    {
        $data = $this->pipeline->send(new GetSubAccountRequest($subAccountId));

        return SubAccount::fromArray($data);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function exists(string $subAccountId): bool
    {
        try {
            $this->pipeline->send(new SubAccountExistsRequest($subAccountId));

            return true;
        } catch (ApaleoNotFoundException) {
            return false;
        }
    }

    /**
     * @return PaginatedResult<SubAccount>
     *
     * @throws ApaleoExceptionInterface
     */
    public function list(string $propertyId, ?int $pageNumber = null, ?int $pageSize = null): PaginatedResult
    {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListSubAccountsRequest($propertyId, $pageNumber, $pageSize));

        return PaginatedResult::fromResponse($data, 'subAccounts', SubAccount::fromArray(...));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function count(string $propertyId): int
    {
        $data = $this->pipeline->send(new CountSubAccountsRequest($propertyId));

        return ResponseData::int($data, 'count');
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function create(CreateSubAccount $data, ?string $idempotencyKey = null): string
    {
        $response = $this->pipeline->send(new CreateSubAccountRequest($data, $idempotencyKey));

        return ResponseData::string($response, 'id');
    }

    /**
     * Only the name can be changed.
     *
     * @throws ApaleoExceptionInterface
     */
    public function update(string $subAccountId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateSubAccountRequest($subAccountId, $patch));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function delete(string $subAccountId): void
    {
        $this->pipeline->send(new DeleteSubAccountRequest($subAccountId));
    }
}
