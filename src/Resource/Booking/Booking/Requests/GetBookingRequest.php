<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Support\Query;

final readonly class GetBookingRequest extends Request
{
    /** @param list<'property'|'propertyValues'|'ratePlan'|'reservations'|'services'|'unitGroup'> $expand */
    public function __construct(
        private string $bookingId,
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/bookings/'.rawurlencode($this->bookingId);
    }

    public function query(): array
    {
        return array_filter(['expand' => Query::csv($this->expand)], static fn (mixed $value): bool => $value !== null);
    }
}
