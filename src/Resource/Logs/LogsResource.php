<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Logs\DTO\FolioChangeLogItem;
use Oleksyuk\Apaleo\Resource\Logs\DTO\NightAuditLogItem;
use Oleksyuk\Apaleo\Resource\Logs\DTO\ReservationChangeLogItem;
use Oleksyuk\Apaleo\Resource\Logs\DTO\TransactionsExportLogItem;
use Oleksyuk\Apaleo\Resource\Logs\Enum\FolioLogEventType;
use Oleksyuk\Apaleo\Resource\Logs\Enum\NightAuditStatus;
use Oleksyuk\Apaleo\Resource\Logs\Enum\ReservationLogEventType;
use Oleksyuk\Apaleo\Resource\Logs\Enum\TransactionsExportType;
use Oleksyuk\Apaleo\Resource\Logs\Requests\ListFolioChangeLogsRequest;
use Oleksyuk\Apaleo\Resource\Logs\Requests\ListNightAuditLogsRequest;
use Oleksyuk\Apaleo\Resource\Logs\Requests\ListReservationChangeLogsRequest;
use Oleksyuk\Apaleo\Resource\Logs\Requests\ListTransactionsExportLogsRequest;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class LogsResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<string>                  $reservationIds
     * @param list<ReservationLogEventType> $eventTypes
     * @param list<string>                  $clientIds
     * @param list<string>                  $propertyIds
     * @param list<string>                  $subjectIds
     * @param list<string>                  $dateFilter expressions like "gte_2024-01-01T00:00:00Z"
     *
     * @return PaginatedResult<ReservationChangeLogItem>
     */
    public function reservationChanges(
        array $reservationIds = [],
        array $eventTypes = [],
        array $clientIds = [],
        array $propertyIds = [],
        array $subjectIds = [],
        array $dateFilter = [],
        ?int $pageNumber = null,
        ?int $pageSize = null,
        bool $expandChanges = false,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListReservationChangeLogsRequest(
            $reservationIds,
            $eventTypes,
            $clientIds,
            $propertyIds,
            $subjectIds,
            $dateFilter,
            $pageNumber,
            $pageSize,
            $expandChanges,
        ));

        return new PaginatedResult(
            items: array_map(ReservationChangeLogItem::fromArray(...), ResponseData::nestedList($data, 'logEntries')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    /**
     * @param list<string>            $folioIds
     * @param list<FolioLogEventType> $eventTypes
     * @param list<string>            $clientIds
     * @param list<string>            $propertyIds
     * @param list<string>            $subjectIds
     * @param list<string>            $dateFilter expressions like "gte_2024-01-01T00:00:00Z"
     *
     * @return PaginatedResult<FolioChangeLogItem>
     */
    public function folioChanges(
        array $folioIds = [],
        array $eventTypes = [],
        array $clientIds = [],
        array $propertyIds = [],
        array $subjectIds = [],
        array $dateFilter = [],
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListFolioChangeLogsRequest($folioIds, $eventTypes, $clientIds, $propertyIds, $subjectIds, $dateFilter, $pageNumber, $pageSize));

        return new PaginatedResult(
            items: array_map(FolioChangeLogItem::fromArray(...), ResponseData::nestedList($data, 'logEntries')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    /**
     * @param list<NightAuditStatus> $statuses
     * @param list<string>           $propertyIds
     * @param list<string>           $subjectIds
     * @param list<string>           $dateFilter expressions like "gte_2024-01-01T00:00:00Z"
     *
     * @return PaginatedResult<NightAuditLogItem>
     */
    public function nightAudit(
        array $statuses = [],
        array $propertyIds = [],
        array $subjectIds = [],
        array $dateFilter = [],
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListNightAuditLogsRequest($statuses, $propertyIds, $subjectIds, $dateFilter, $pageNumber, $pageSize));

        return new PaginatedResult(
            items: array_map(NightAuditLogItem::fromArray(...), ResponseData::nestedList($data, 'logEntries')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    /**
     * @param list<TransactionsExportType> $types
     * @param list<string>                 $propertyIds
     * @param list<string>                 $subjectIds
     * @param list<string>                 $dateFilter expressions like "gte_2024-01-01T00:00:00Z"
     *
     * @return PaginatedResult<TransactionsExportLogItem>
     */
    public function transactionsExport(
        array $types = [],
        array $propertyIds = [],
        array $subjectIds = [],
        array $dateFilter = [],
        ?int $pageNumber = null,
        ?int $pageSize = null,
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListTransactionsExportLogsRequest($types, $propertyIds, $subjectIds, $dateFilter, $pageNumber, $pageSize));

        return new PaginatedResult(
            items: array_map(TransactionsExportLogItem::fromArray(...), ResponseData::nestedList($data, 'logEntries')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }
}
