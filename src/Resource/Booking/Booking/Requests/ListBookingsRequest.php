<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;

final class ListBookingsRequest extends Request
{
    /**
     * @param list<string>      $bookingIds
     * @param list<ChannelCode> $channelCode
     * @param list<string>      $expand
     */
    public function __construct(
        private readonly ?string $reservationId = null,
        private readonly ?string $groupId = null,
        private readonly array $bookingIds = [],
        private readonly array $channelCode = [],
        private readonly ?string $externalCode = null,
        private readonly ?string $textSearch = null,
        private readonly ?bool $hasActivePaymentAccount = null,
        private readonly ?int $pageNumber = null,
        private readonly ?int $pageSize = null,
        private readonly array $expand = [],
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
