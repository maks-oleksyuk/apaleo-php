<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Booking\BookingFilter;

final readonly class ListBookingsRequest extends Request
{
    /** @param list<'property'|'ratePlan'|'reservations'|'services'|'unitGroup'> $expand */
    public function __construct(
        private BookingFilter $filter = new BookingFilter(),
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/bookings';
    }

    public function query(): array
    {
        return array_filter([
            ...$this->filter->toQuery(),
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
            'expand' => implode(',', $this->expand) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
