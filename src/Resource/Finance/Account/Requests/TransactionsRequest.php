<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Account\TransactionFilter;

/** Shared shape for the transaction exports and aggregations: POSTs that only take query params. */
final readonly class TransactionsRequest extends Request
{
    /** @param 'aggregate'|'aggregate-daily'|'aggregate-pairs-daily'|'export'|'export-daily'|'export-gross-daily' $operation */
    public function __construct(
        private string $operation,
        private TransactionFilter $filter,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/finance/v1/accounts/'.$this->operation;
    }

    public function query(): array
    {
        return $this->filter->toQuery(str_ends_with($this->operation, '-daily'));
    }
}
