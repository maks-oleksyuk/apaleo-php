<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Group\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Group\DTO\PickUpReservation;

final readonly class PickUpReservationsRequest extends Request
{
    /** @param list<PickUpReservation> $reservations */
    public function __construct(
        private string $groupId,
        private array $reservations,
    ) {}

    public function method(): Method
    {
        return Method::POST;
    }

    public function endpoint(): string
    {
        return '/booking/v1/groups/'.rawurlencode($this->groupId).'/reservations';
    }

    public function body(): array
    {
        return ['reservations' => array_map(static fn (PickUpReservation $r): array => $r->toArray(), $this->reservations)];
    }
}
