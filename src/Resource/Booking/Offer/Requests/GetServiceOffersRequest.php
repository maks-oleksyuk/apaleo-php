<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;

final readonly class GetServiceOffersRequest extends Request
{
    /** @param list<int> $childrenAges */
    public function __construct(
        private string $ratePlanId,
        private \DateTimeImmutable $arrival,
        private \DateTimeImmutable $departure,
        private int $adults,
        private ?ChannelCode $channelCode = null,
        private array $childrenAges = [],
        private ?bool $onlyDefaultDates = null,
        private ?bool $includeUnavailable = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/service-offers';
    }

    public function query(): array
    {
        return array_filter([
            'ratePlanId' => $this->ratePlanId,
            'arrival' => $this->arrival->format('Y-m-d'),
            'departure' => $this->departure->format('Y-m-d'),
            'adults' => $this->adults,
            'channelCode' => $this->channelCode?->value,
            'childrenAges' => implode(',', $this->childrenAges) ?: null,
            'onlyDefaultDates' => $this->onlyDefaultDates,
            'includeUnavailable' => $this->includeUnavailable,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
