<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;

final readonly class ListBookingsRequest extends Request
{
    /**
     * @param list<string>      $bookingIds
     * @param list<ChannelCode> $channelCode
     * @param list<string>      $expand
     */
    public function __construct(
        private ?string $reservationId = null,
        private ?string $groupId = null,
        private array $bookingIds = [],
        private array $channelCode = [],
        private ?string $externalCode = null,
        private ?string $textSearch = null,
        private ?bool $hasActivePaymentAccount = null,
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
        private array $expand = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/bookings';
    }

    public function query(): array
    {
        return array_filter([
            'reservationId' => $this->reservationId,
            'groupId' => $this->groupId,
            'bookingIds' => implode(',', $this->bookingIds) ?: null,
            'channelCode' => implode(',', array_map(static fn (ChannelCode $c): string => $c->value, $this->channelCode)) ?: null,
            'externalCode' => $this->externalCode,
            'textSearch' => $this->textSearch,
            'hasActivePaymentAccount' => $this->hasActivePaymentAccount,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
            'expand' => implode(',', $this->expand) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
