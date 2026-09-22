<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization;

use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationStatus;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationTargetType;

final readonly class AuthorizationFilter
{
    /**
     * @param list<string>                 $propertyIds
     * @param list<string>                 $bookingIds
     * @param list<string>                 $reservationIds
     * @param list<AuthorizationStatus>    $status
     * @param list<AuthorizationTargetType> $targetTypes
     */
    public function __construct(
        public array $propertyIds = [],
        public array $bookingIds = [],
        public array $reservationIds = [],
        public array $status = [],
        public array $targetTypes = [],
        public ?string $dateField = null,
        public ?\DateTimeImmutable $from = null,
        public ?\DateTimeImmutable $to = null,
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'propertyIds' => implode(',', $this->propertyIds) ?: null,
            'bookingIds' => implode(',', $this->bookingIds) ?: null,
            'reservationIds' => implode(',', $this->reservationIds) ?: null,
            'status' => implode(',', array_map(static fn (AuthorizationStatus $s): string => $s->value, $this->status)) ?: null,
            'targetTypes' => implode(',', array_map(static fn (AuthorizationTargetType $t): string => $t->value, $this->targetTypes)) ?: null,
            'dateField' => $this->dateField,
            'from' => $this->from?->format(\DateTimeInterface::ATOM),
            'to' => $this->to?->format(\DateTimeInterface::ATOM),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
