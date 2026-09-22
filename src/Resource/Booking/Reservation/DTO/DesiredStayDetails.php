<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

final readonly class DesiredStayDetails
{
    /**
     * @param list<int>              $childrenAges
     * @param list<DesiredTimeSlice> $timeSlices
     */
    public function __construct(
        public string $arrival,
        public string $departure,
        public int $adults,
        public array $timeSlices,
        public array $childrenAges = [],
        public ?bool $requote = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'arrival' => $this->arrival,
            'departure' => $this->departure,
            'adults' => $this->adults,
            'childrenAges' => $this->childrenAges !== [] ? $this->childrenAges : null,
            'requote' => $this->requote,
            'timeSlices' => array_map(static fn (DesiredTimeSlice $s): array => $s->toArray(), $this->timeSlices),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
