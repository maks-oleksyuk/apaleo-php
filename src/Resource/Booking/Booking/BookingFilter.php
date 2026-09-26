<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking;

use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Support\Query;

final readonly class BookingFilter
{
    /**
     * @param list<string>      $bookingIds
     * @param list<ChannelCode> $channelCode
     */
    public function __construct(
        public ?string $reservationId = null,
        public ?string $groupId = null,
        public array $bookingIds = [],
        public array $channelCode = [],
        public ?string $externalCode = null,
        public ?string $textSearch = null,
        public ?bool $hasActivePaymentAccount = null,
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'reservationId' => $this->reservationId,
            'groupId' => $this->groupId,
            'bookingIds' => Query::csv($this->bookingIds),
            'channelCode' => Query::csv($this->channelCode),
            'externalCode' => $this->externalCode,
            'textSearch' => $this->textSearch,
            'hasActivePaymentAccount' => $this->hasActivePaymentAccount,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
