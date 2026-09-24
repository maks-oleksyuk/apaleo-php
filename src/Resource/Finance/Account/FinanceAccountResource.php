<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account;

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

    /** @param ?int $transactionLimit how many of the latest transactions to include */
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

    /** @param ?int $depth how many levels of sub-accounts to include */
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

    /** @return PaginatedResult<FinanceAccountListItem> */
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

    /** @return PaginatedResult<FinanceAccountListItem> */
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

    /** @return PaginatedResult<FinanceAccountListItem> */
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

    /** @return PaginatedResult<FinanceAccountListItem> */
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

    /** @return list<AccountingTransaction> */
    public function export(TransactionFilter $filter): array
    {
        return $this->transactions('export', $filter);
    }

    /** @return list<AccountingTransaction> */
    public function exportDaily(TransactionFilter $filter): array
    {
        return $this->transactions('export-daily', $filter);
    }

    /**
     * Only $propertyId, $from, $to, $reference and $accountingSchema of the filter apply.
     *
     * @return list<GrossTransaction>
     */
    public function exportGrossDaily(TransactionFilter $filter): array
    {
        $data = $this->pipeline->send(new TransactionsRequest('export-gross-daily', $filter));

        return array_map(GrossTransaction::fromArray(...), ResponseData::nestedList($data, 'transactions'));
    }

    public function aggregate(TransactionFilter $filter): TransactionAggregates
    {
        return TransactionAggregates::fromArray($this->pipeline->send(new TransactionsRequest('aggregate', $filter)));
    }

    public function aggregateDaily(TransactionFilter $filter): TransactionAggregates
    {
        return TransactionAggregates::fromArray($this->pipeline->send(new TransactionsRequest('aggregate-daily', $filter)));
    }

    /** @return list<TransactionPair> */
    public function aggregatePairsDaily(TransactionFilter $filter): array
    {
        $data = $this->pipeline->send(new TransactionsRequest('aggregate-pairs-daily', $filter));

        return array_map(TransactionPair::fromArray(...), ResponseData::nestedList($data, 'accountTransactionPairs'));
    }

    /**
     * @param 'export'|'export-daily' $operation
     *
     * @return list<AccountingTransaction>
     */
    private function transactions(string $operation, TransactionFilter $filter): array
    {
        $data = $this->pipeline->send(new TransactionsRequest($operation, $filter));

        return array_map(AccountingTransaction::fromArray(...), ResponseData::nestedList($data, 'transactions'));
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return PaginatedResult<FinanceAccountListItem>
     */
    private function accounts(array $data): PaginatedResult
    {
        return new PaginatedResult(
            items: array_map(FinanceAccountListItem::fromArray(...), ResponseData::nestedList($data, 'accounts')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }
}
