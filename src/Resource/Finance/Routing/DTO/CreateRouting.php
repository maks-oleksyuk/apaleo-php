<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Routing\DTO;

final readonly class CreateRouting
{
    public function __construct(
        public string $bookingId,
        public string $propertyId,
        public string $destinationFolioId,
        public ?CreateRoutingChargeFilter $filter = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'bookingId' => $this->bookingId,
            'propertyId' => $this->propertyId,
            'destinationFolioId' => $this->destinationFolioId,
            'filter' => $this->filter?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
