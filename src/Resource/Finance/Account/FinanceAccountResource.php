<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Finance\Account\DTO\AccountingTransaction;
use Oleksyuk\Apaleo\Resource\Finance\Account\DTO\ChartOfAccounts;
use Oleksyuk\Apaleo\Resource\Finance\Account\DTO\FinanceAccount;
use Oleksyuk\Apaleo\Resource\Finance\Account\DTO\FinanceAccountListItem;
use Oleksyuk\Apaleo\Resource\Finance\Account\DTO\GrossTransaction;
use Oleksyuk\Apaleo\Resource\Finance\Account\DTO\TransactionAggregates;
use Oleksyuk\Apaleo\Resource\Finance\Account\DTO\TransactionPair;
use Oleksyuk\Apaleo\Resource\Finance\Account\Enum\AccountingSchema;
use Oleksyuk\Apaleo\Resource\Finance\Account\Requests\GetChartOfAccountsRequest;
use Oleksyuk\Apaleo\Resource\Finance\Account\Requests\GetFinanceAccountRequest;
use Oleksyuk\Apaleo\Resource\Finance\Account\Requests\ListChildAccountsRequest;
use Oleksyuk\Apaleo\Resource\Finance\Account\Requests\ListExternalAccountsRequest;
use Oleksyuk\Apaleo\Resource\Finance\Account\Requests\ListGlobalAccountsRequest;
use Oleksyuk\Apaleo\Resource\Finance\Account\Requests\ListGuestAccountsRequest;
use Oleksyuk\Apaleo\Resource\Finance\Account\Requests\TransactionsRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Methods ending in Daily filter by business day, the others by exact timestamp. */
final readonly class FinanceAccountResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param ?int $transactionLimit how many of the latest transactions to include
     *
     * @throws ApaleoExceptionInterface
     */
    public function get(
        string $propertyId,
        string $accountNumber,
        ?int $transactionLimit = null,
        ?bool $includeArchived = null,
        ?AccountingSchema $accountingSchema = null,
        ?string $languageCode = null,
    ): FinanceAccount {
        $data = $this->pipeline->send(new GetFinanceAccountRequest($propertyId, $accountNumber, $transactionLimit, $includeArchived, $accountingSchema, $languageCode));

        return FinanceAccount::fromArray($data);
    }

    /**
     * @param ?int $depth how many levels of sub-accounts to include
     *
     * @throws ApaleoExceptionInterface
     */
    public function chartOfAccounts(
        string $propertyId,
        ?int $depth = null,
        ?bool $includeArchived = null,
        ?AccountingSchema $accountingSchema = null,
        ?string $languageCode = null,
    ): ChartOfAccounts {
        $data = $this->pipeline->send(new GetChartOfAccountsRequest($propertyId, $depth, $includeArchived, $accountingSchema, $languageCode));

        return ChartOfAccounts::fromArray($data);
    }

    /**
     * @return PaginatedResult<FinanceAccountListItem>
     *
     * @throws ApaleoExceptionInterface
     */
    public function globalAccounts(
        string $propertyId,
        string $parent,
        ?bool $includeArchived = null,
        ?AccountingSchema $accountingSchema = null,
        ?string $languageCode = null,
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        return $this->accounts($this->pipeline->send(new ListGlobalAccountsRequest($propertyId, $parent, $includeArchived, $accountingSchema, $languageCode, $pageNumber, $pageSize)));
    }

    /**
     * @return PaginatedResult<FinanceAccountListItem>
     *
     * @throws ApaleoExceptionInterface
     */
    public function childAccounts(
        string $propertyId,
        string $parent,
        ?bool $includeArchived = null,
        ?AccountingSchema $accountingSchema = null,
        ?string $languageCode = null,
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        return $this->accounts($this->pipeline->send(new ListChildAccountsRequest($propertyId, $parent, $includeArchived, $accountingSchema, $languageCode, $pageNumber, $pageSize)));
    }

    /**
     * @return PaginatedResult<FinanceAccountListItem>
     *
     * @throws ApaleoExceptionInterface
     */
    public function guestAccounts(
        string $propertyId,
        string $reservationId,
        ?string $parent = null,
        ?string $languageCode = null,
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        return $this->accounts($this->pipeline->send(new ListGuestAccountsRequest($propertyId, $reservationId, $parent, $languageCode, $pageNumber, $pageSize)));
    }

    /**
     * @return PaginatedResult<FinanceAccountListItem>
     *
     * @throws ApaleoExceptionInterface
     */
    public function externalAccounts(
        string $propertyId,
        string $folioId,
        ?string $parent = null,
        ?string $languageCode = null,
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        return $this->accounts($this->pipeline->send(new ListExternalAccountsRequest($propertyId, $folioId, $parent, $languageCode, $pageNumber, $pageSize)));
    }

    /**
     * @return list<AccountingTransaction>
     *
     * @throws ApaleoExceptionInterface
     */
    public function export(TransactionFilter $filter): array
    {
        return $this->transactions('export', $filter);
    }

    /**
     * @return list<AccountingTransaction>
     *
     * @throws ApaleoExceptionInterface
     */
    public function exportDaily(TransactionFilter $filter): array
    {
        return $this->transactions('export-daily', $filter);
    }

    /**
     * Only $propertyId, $from, $to, $reference and $accountingSchema of the filter apply.
     *
     * @return list<GrossTransaction>
     *
     * @throws ApaleoExceptionInterface
     */
    public function exportGrossDaily(TransactionFilter $filter): array
    {
        $data = $this->pipeline->send(new TransactionsRequest('export-gross-daily', $filter));

        return ResponseData::mapList($data, 'transactions', GrossTransaction::fromArray(...));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function aggregate(TransactionFilter $filter): TransactionAggregates
    {
        return TransactionAggregates::fromArray($this->pipeline->send(new TransactionsRequest('aggregate', $filter)));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function aggregateDaily(TransactionFilter $filter): TransactionAggregates
    {
        return TransactionAggregates::fromArray($this->pipeline->send(new TransactionsRequest('aggregate-daily', $filter)));
    }

    /**
     * @return list<TransactionPair>
     *
     * @throws ApaleoExceptionInterface
     */
    public function aggregatePairsDaily(TransactionFilter $filter): array
    {
        $data = $this->pipeline->send(new TransactionsRequest('aggregate-pairs-daily', $filter));

        return ResponseData::mapList($data, 'accountTransactionPairs', TransactionPair::fromArray(...));
    }

    /**
     * @param 'export'|'export-daily' $operation
     *
     * @return list<AccountingTransaction>
     */
    private function transactions(string $operation, TransactionFilter $filter): array
    {
        $data = $this->pipeline->send(new TransactionsRequest($operation, $filter));

        return ResponseData::mapList($data, 'transactions', AccountingTransaction::fromArray(...));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return PaginatedResult<FinanceAccountListItem>
     */
    private function accounts(array $data): PaginatedResult
    {
        return PaginatedResult::fromResponse($data, 'accounts', FinanceAccountListItem::fromArray(...));
    }
}
