<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Logs\Enum\ReservationLogEventType;

final readonly class ListReservationChangeLogsRequest extends Request
{
    /**
     * @param list<string>                   $reservationIds
     * @param list<ReservationLogEventType>  $eventTypes
     * @param list<string>                   $clientIds
     * @param list<string>                   $propertyIds
     * @param list<string>                   $subjectIds
     * @param list<string>                   $dateFilter expressions like "gte_2024-01-01T00:00:00Z"
     */
    public function __construct(
        private array $reservationIds = [],
        private array $eventTypes = [],
        private array $clientIds = [],
        private array $propertyIds = [],
        private array $subjectIds = [],
        private array $dateFilter = [],
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
        private bool $expandChanges = false,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/logs/v1/booking/reservation';
    }

    public function query(): array
    {
        return array_filter([
            'reservationIds' => implode(',', $this->reservationIds) ?: null,
            'eventTypes' => implode(',', array_map(static fn (ReservationLogEventType $t): string => $t->value, $this->eventTypes)) ?: null,
            'clientIds' => implode(',', $this->clientIds) ?: null,
            'propertyIds' => implode(',', $this->propertyIds) ?: null,
            'subjectIds' => implode(',', $this->subjectIds) ?: null,
            'dateFilter' => implode(',', $this->dateFilter) ?: null,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
            'expand' => $this->expandChanges ? 'changes' : null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
