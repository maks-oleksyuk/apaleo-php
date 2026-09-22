<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Logs\Enum\TransactionsExportType;

final readonly class ListTransactionsExportLogsRequest extends Request
{
    /**
     * @param list<TransactionsExportType> $types
     * @param list<string>                 $propertyIds
     * @param list<string>                 $subjectIds
     * @param list<string>                 $dateFilter expressions like "gte_2024-01-01T00:00:00Z"
     */
    public function __construct(
        private array $types = [],
        private array $propertyIds = [],
        private array $subjectIds = [],
        private array $dateFilter = [],
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/logs/v1/finance/transactions-export';
    }

    public function query(): array
    {
        return array_filter([
            'types' => implode(',', array_map(static fn (TransactionsExportType $t): string => $t->value, $this->types)) ?: null,
            'propertyIds' => implode(',', $this->propertyIds) ?: null,
            'subjectIds' => implode(',', $this->subjectIds) ?: null,
            'dateFilter' => implode(',', $this->dateFilter) ?: null,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
