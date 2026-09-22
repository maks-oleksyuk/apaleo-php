<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Group\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\BookReservationService;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Guest;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\TravelPurpose;

/** Picks up a reservation from a block already blocked for a {@see Group}. */
final readonly class PickUpReservation
{
    /**
     * @param list<int>                    $childrenAges
     * @param list<BookReservationService>  $services
     * @param list<Guest>                   $additionalGuests
     */
    public function __construct(
        public string $blockId,
        public string $arrival,
        public string $departure,
        public int $adults,
        public array $childrenAges = [],
        public array $services = [],
        public ?string $comment = null,
        public ?string $guestComment = null,
        public ?Guest $primaryGuest = null,
        public array $additionalGuests = [],
        public ?TravelPurpose $travelPurpose = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'blockId' => $this->blockId,
            'arrival' => $this->arrival,
            'departure' => $this->departure,
            'adults' => $this->adults,
            'childrenAges' => $this->childrenAges !== [] ? $this->childrenAges : null,
            'services' => $this->services !== [] ? array_map(static fn (BookReservationService $s): array => $s->toArray(), $this->services) : null,
            'comment' => $this->comment,
            'guestComment' => $this->guestComment,
            'primaryGuest' => $this->primaryGuest?->toArray(),
            'additionalGuests' => $this->additionalGuests !== [] ? array_map(static fn (Guest $g): array => $g->toArray(), $this->additionalGuests) : null,
            'travelPurpose' => $this->travelPurpose?->value,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
