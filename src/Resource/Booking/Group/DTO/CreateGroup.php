<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Group\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Booker;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\RegisteredCard;

/**
 * The payload for creating a group booking. Does not expose the deprecated inline `paymentAccount`
 * field (superseded by the dedicated PaymentAccounts resource, being removed 2026-05-15).
 */
final readonly class CreateGroup
{
    /** @param list<string> $propertyIds */
    public function __construct(
        public string $name,
        public Booker $booker,
        public array $propertyIds,
        public ?RegisteredCard $registeredCard = null,
        public ?string $comment = null,
        public ?string $bookerComment = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'booker' => $this->booker->toArray(),
            'propertyIds' => $this->propertyIds,
            'registeredCard' => $this->registeredCard?->toArray(),
            'comment' => $this->comment,
            'bookerComment' => $this->bookerComment,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
