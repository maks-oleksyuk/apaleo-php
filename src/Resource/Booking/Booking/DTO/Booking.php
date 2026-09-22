<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Booker;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\RegisteredCard;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Booking
{
    /**
     * @param list<PropertyValue>     $propertyValues
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
        public array $propertyValues,
        public array $reservations,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $booker = ResponseData::nested($data, 'booker');
        $registeredCard = ResponseData::nested($data, 'registeredCard');

        return new self(
            id: ResponseData::string($data, 'id'),
            groupId: ResponseData::nullableString($data, 'groupId'),
            booker: $booker !== [] ? Booker::fromArray($booker) : null,
            hasActivePaymentAccount: ResponseData::bool($data, 'hasActivePaymentAccount'),
            registeredCard: $registeredCard !== [] ? RegisteredCard::fromArray($registeredCard) : null,
            comment: ResponseData::nullableString($data, 'comment'),
            bookerComment: ResponseData::nullableString($data, 'bookerComment'),
            created: ResponseData::dateTime($data, 'created'),
            modified: ResponseData::dateTime($data, 'modified'),
            propertyValues: array_map(PropertyValue::fromArray(...), ResponseData::nestedList($data, 'propertyValues')),
            reservations: array_map(BookingReservation::fromArray(...), ResponseData::nestedList($data, 'reservations')),
        );
    }
}
