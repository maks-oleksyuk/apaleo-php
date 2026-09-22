<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\ReservationFilter;

final class ListReservationsRequest extends Request
{
    /**
     * @param list<string> $sort
     * @param list<string> $expand
     */
    public function __construct(
        private readonly ReservationFilter $filter,
        private readonly ?int $pageNumber = null,
        private readonly ?int $pageSize = null,
        private readonly array $sort = [],
        private readonly array $expand = [],
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
            'sort' => implode(',', $this->sort) ?: null,
            'expand' => implode(',', $this->expand) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
