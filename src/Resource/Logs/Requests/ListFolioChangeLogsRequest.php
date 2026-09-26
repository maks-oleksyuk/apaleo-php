<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Logs\Enum\FolioLogEventType;
use Oleksyuk\Apaleo\Support\Query;

final readonly class ListFolioChangeLogsRequest extends Request
{
    /**
     * @param list<string>            $folioIds
     * @param list<FolioLogEventType> $eventTypes
     * @param list<string>            $clientIds
     * @param list<string>            $propertyIds
     * @param list<string>            $subjectIds
     * @param list<string>            $dateFilter expressions like "gte_2024-01-01T00:00:00Z"
     */
    public function __construct(
        private array $folioIds = [],
        private array $eventTypes = [],
        private array $clientIds = [],
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
        return '/logs/v1/finance/folio';
    }

    public function query(): array
    {
        return array_filter([
            'folioIds' => Query::csv($this->folioIds),
            'eventTypes' => Query::csv($this->eventTypes),
            'clientIds' => Query::csv($this->clientIds),
            'propertyIds' => Query::csv($this->propertyIds),
            'subjectIds' => Query::csv($this->subjectIds),
            'dateFilter' => Query::csv($this->dateFilter),
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
