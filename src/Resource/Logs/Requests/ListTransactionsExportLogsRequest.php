<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Logs\Enum\TransactionsExportType;
use Oleksyuk\Apaleo\Support\Query;

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
            'types' => Query::csv($this->types),
            'propertyIds' => Query::csv($this->propertyIds),
            'subjectIds' => Query::csv($this->subjectIds),
            'dateFilter' => Query::csv($this->dateFilter),
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
