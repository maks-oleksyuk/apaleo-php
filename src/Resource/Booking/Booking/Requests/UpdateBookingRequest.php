<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;

final readonly class UpdateBookingRequest extends Request
{
    public function __construct(
        private string $bookingId,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/booking/v1/bookings/'.rawurlencode($this->bookingId);
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
