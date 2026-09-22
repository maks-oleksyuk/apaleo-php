<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;

final readonly class OfferIndexRequest extends Request
{
    public function __construct(
        private string $ratePlanId,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to,
        private ChannelCode $channelCode,
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/offer-index';
    }

    public function query(): array
    {
        return array_filter([
            'ratePlanId' => $this->ratePlanId,
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'channelCode' => $this->channelCode->value,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
