<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\DTO;

final readonly class CreateBlockTimeSlice
{
    public function __construct(
        public int $blockedUnits,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['blockedUnits' => $this->blockedUnits];
    }
}
