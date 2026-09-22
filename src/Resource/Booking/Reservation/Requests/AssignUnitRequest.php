<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

/** Auto-assigns a unit for the whole reservation stay; see {@see AssignSpecificUnitRequest} to pick the unit yourself. */
final class AssignUnitRequest extends Request
{
    /** @param list<string> $unitConditions allowed: Clean, CleanToBeInspected, Dirty */
    public function __construct(
        private readonly string $reservationId,
        private readonly array $unitConditions = [],
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/booking/v1/reservation-actions/'.rawurlencode($this->reservationId).'/assign-unit';
    }

    public function query(): array
    {
        return array_filter(['unitConditions' => implode(',', $this->unitConditions) ?: null], static fn (mixed $value): bool => $value !== null);
    }
}
