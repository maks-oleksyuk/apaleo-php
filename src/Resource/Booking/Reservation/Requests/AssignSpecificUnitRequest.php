<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;

final class AssignSpecificUnitRequest extends Request
{
    public function __construct(
        private readonly string $reservationId,
        private readonly string $unitId,
        private readonly ?\DateTimeImmutable $from = null,
        private readonly ?\DateTimeImmutable $to = null,
        private readonly ?bool $lockUnit = null,
    ) {}

    public function method(): Method
    {
        return Method::PUT;
    }

    public function endpoint(): string
    {
        return '/booking/v1/reservation-actions/'.rawurlencode($this->reservationId).'/assign-unit/'.rawurlencode($this->unitId);
    }

    public function query(): array
    {
        return array_filter([
            'from' => $this->from?->format(\DateTimeInterface::ATOM),
            'to' => $this->to?->format(\DateTimeInterface::ATOM),
            'lockUnit' => $this->lockUnit,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
