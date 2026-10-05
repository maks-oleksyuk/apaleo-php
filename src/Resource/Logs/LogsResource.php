<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
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
     *
     * @throws ApaleoExceptionInterface
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

        return PaginatedResult::fromResponse($data, 'logEntries', ReservationChangeLogItem::fromArray(...));
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
     *
     * @throws ApaleoExceptionInterface
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

        return PaginatedResult::fromResponse($data, 'logEntries', FolioChangeLogItem::fromArray(...));
    }

    /**
     * @param list<NightAuditStatus> $statuses
     * @param list<string>           $propertyIds
     * @param list<string>           $subjectIds
     * @param list<string>           $dateFilter expressions like "gte_2024-01-01T00:00:00Z"
     *
     * @return PaginatedResult<NightAuditLogItem>
     *
     * @throws ApaleoExceptionInterface
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

        return PaginatedResult::fromResponse($data, 'logEntries', NightAuditLogItem::fromArray(...));
    }

    /**
     * @param list<TransactionsExportType> $types
     * @param list<string>                 $propertyIds
     * @param list<string>                 $subjectIds
     * @param list<string>                 $dateFilter expressions like "gte_2024-01-01T00:00:00Z"
     *
     * @return PaginatedResult<TransactionsExportLogItem>
     *
     * @throws ApaleoExceptionInterface
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

        return PaginatedResult::fromResponse($data, 'logEntries', TransactionsExportLogItem::fromArray(...));
    }
}
