<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;

final readonly class GetRatePlanOffersRequest extends Request
{
    /**
     * @param list<int>   $childrenAges
     * @param list<float> $overridePrices
     */
    public function __construct(
        private string $ratePlanId,
        private string $arrival,
        private string $departure,
        private int $adults,
        private ?ChannelCode $channelCode = null,
        private array $childrenAges = [],
        private ?bool $includeUnavailable = null,
        private array $overridePrices = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/rate-plan-offers';
    }

    public function query(): array
    {
        return array_filter([
            'ratePlanId' => $this->ratePlanId,
            'arrival' => $this->arrival,
            'departure' => $this->departure,
            'adults' => $this->adults,
            'channelCode' => $this->channelCode?->value,
            'childrenAges' => implode(',', $this->childrenAges) ?: null,
            'includeUnavailable' => $this->includeUnavailable,
            'overridePrices' => implode(',', $this->overridePrices) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
