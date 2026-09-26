<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\ReservationFilter;
use Oleksyuk\Apaleo\Support\Query;

final readonly class ListReservationsRequest extends Request
{
    /**
     * @param list<string> $sort
     * @param list<'actions'|'assignedUnits'|'booker'|'company'|'services'|'timeSlices'> $expand
     */
    public function __construct(
        private ReservationFilter $filter,
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
        private array $sort = [],
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/reservations';
    }

    public function query(): array
    {
        return array_filter([
            ...$this->filter->toQuery(),
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
            'sort' => Query::csv($this->sort),
            'expand' => Query::csv($this->expand),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
