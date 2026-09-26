<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Authorization;

use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationStatus;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\Enum\AuthorizationTargetType;
use Oleksyuk\Apaleo\Support\Query;

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
            'propertyIds' => Query::csv($this->propertyIds),
            'bookingIds' => Query::csv($this->bookingIds),
            'reservationIds' => Query::csv($this->reservationIds),
            'status' => Query::csv($this->status),
            'targetTypes' => Query::csv($this->targetTypes),
            'dateField' => $this->dateField,
            'from' => $this->from?->format(\DateTimeInterface::ATOM),
            'to' => $this->to?->format(\DateTimeInterface::ATOM),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
