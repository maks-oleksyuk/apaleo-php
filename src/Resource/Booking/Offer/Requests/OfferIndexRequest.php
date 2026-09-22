<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;

final class OfferIndexRequest extends Request
{
    public function __construct(
        private readonly string $ratePlanId,
        private readonly \DateTimeImmutable $from,
        private readonly \DateTimeImmutable $to,
        private readonly ChannelCode $channelCode,
        private readonly ?int $pageNumber = null,
        private readonly ?int $pageSize = null,
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
            'from' => $this->from->format(\DateTimeInterface::ATOM),
            'to' => $this->to->format(\DateTimeInterface::ATOM),
            'channelCode' => $this->channelCode->value,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
