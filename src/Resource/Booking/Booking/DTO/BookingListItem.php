<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Booker;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\RegisteredCard;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class BookingListItem
{
    /**
     * @param list<BookingReservation> $reservations
     */
    public function __construct(
        public string $id,
        public ?string $groupId,
        public ?Booker $booker,
        public bool $hasActivePaymentAccount,
        public ?RegisteredCard $registeredCard,
        public ?string $comment,
        public ?string $bookerComment,
        public \DateTimeImmutable $created,
        public \DateTimeImmutable $modified,
        public array $reservations,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            groupId: ResponseData::nullableString($data, 'groupId'),
            booker: ResponseData::nullableNested($data, 'booker', Booker::fromArray(...)),
            hasActivePaymentAccount: ResponseData::bool($data, 'hasActivePaymentAccount'),
            registeredCard: ResponseData::nullableNested($data, 'registeredCard', RegisteredCard::fromArray(...)),
            comment: ResponseData::nullableString($data, 'comment'),
            bookerComment: ResponseData::nullableString($data, 'bookerComment'),
            created: ResponseData::dateTime($data, 'created'),
            modified: ResponseData::dateTime($data, 'modified'),
            reservations: array_map(BookingReservation::fromArray(...), ResponseData::nestedList($data, 'reservations')),
        );
    }
}
