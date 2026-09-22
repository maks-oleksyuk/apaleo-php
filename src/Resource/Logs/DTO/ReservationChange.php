<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\ReservationValidationMessage;
use Oleksyuk\Apaleo\Resource\Logs\Enum\ReservationChangeType;
use Oleksyuk\Apaleo\Support\ResponseData;

/**
 * One entry in a ReservationChangeLogItem's "changes" list. Which of the *Value properties is
 * populated depends on $changeType — apaleo models this as one object with ~14 optional
 * "old"/"new" slots rather than a tagged union, so this DTO mirrors that shape as-is.
 */
final readonly class ReservationChange
{
    public function __construct(
        public ReservationChangeType $changeType,
        public ?ReservationAddedChange $reservationAddedValue,
        public ?ReservationChangedChange $newReservationChangedValue,
        public ?ReservationChangedChange $oldReservationChangedValue,
        public ?PersonChange $newAdditionalGuestValue,
        public ?PersonChange $oldAdditionalGuestValue,
        public ?TimeSliceChange $newTimeSliceValue,
        public ?TimeSliceChange $oldTimeSliceValue,
        public ?ServiceChange $newExtraServiceValue,
        public ?ServiceChange $oldExtraServiceValue,
        public ?ReservationValidationMessage $newValidationMessageValue,
        public ?ReservationValidationMessage $oldValidationMessageValue,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            changeType: ReservationChangeType::fromApi(ResponseData::string($data, 'changeType')),
            reservationAddedValue: self::maybe($data, 'reservationAddedValue', ReservationAddedChange::fromArray(...)),
            newReservationChangedValue: self::maybe($data, 'newReservationChangedValue', ReservationChangedChange::fromArray(...)),
            oldReservationChangedValue: self::maybe($data, 'oldReservationChangedValue', ReservationChangedChange::fromArray(...)),
            newAdditionalGuestValue: self::maybe($data, 'newAdditionalGuestValue', PersonChange::fromArray(...)),
            oldAdditionalGuestValue: self::maybe($data, 'oldAdditionalGuestValue', PersonChange::fromArray(...)),
            newTimeSliceValue: self::maybe($data, 'newTimeSliceValue', TimeSliceChange::fromArray(...)),
            oldTimeSliceValue: self::maybe($data, 'oldTimeSliceValue', TimeSliceChange::fromArray(...)),
            newExtraServiceValue: self::maybe($data, 'newExtraServiceValue', ServiceChange::fromArray(...)),
            oldExtraServiceValue: self::maybe($data, 'oldExtraServiceValue', ServiceChange::fromArray(...)),
            newValidationMessageValue: self::maybe($data, 'newValidationMessageValue', ReservationValidationMessage::fromArray(...)),
            oldValidationMessageValue: self::maybe($data, 'oldValidationMessageValue', ReservationValidationMessage::fromArray(...)),
        );
    }

    /**
     * @template T
     *
     * @param array<string, mixed> $data
     * @param callable(array<string, mixed>): T $map
     *
     * @return null|T
     */
    private static function maybe(array $data, string $key, callable $map): mixed
    {
        $nested = ResponseData::nested($data, $key);

        return [] !== $nested ? $map($nested) : null;
    }
}
