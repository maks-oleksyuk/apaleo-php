<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;

final class GetOffersRequest extends Request
{
    /**
     * @param list<string> $timeSliceDefinitionIds
     * @param list<string> $unitGroupIds
     * @param list<string> $unitGroupTypes
     * @param list<int>    $childrenAges
     */
    public function __construct(
        private readonly string $propertyId,
        private readonly string $arrival,
        private readonly string $departure,
        private readonly int $adults,
        private readonly ?string $timeSliceTemplate = null,
        private readonly array $timeSliceDefinitionIds = [],
        private readonly array $unitGroupIds = [],
        private readonly array $unitGroupTypes = [],
        private readonly ?ChannelCode $channelCode = null,
        private readonly ?string $promoCode = null,
        private readonly ?string $corporateCode = null,
        private readonly array $childrenAges = [],
        private readonly ?bool $includeUnavailable = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/booking/v1/offers';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'arrival' => $this->arrival,
            'departure' => $this->departure,
            'adults' => $this->adults,
            'timeSliceTemplate' => $this->timeSliceTemplate,
            'timeSliceDefinitionIds' => implode(',', $this->timeSliceDefinitionIds) ?: null,
            'unitGroupIds' => implode(',', $this->unitGroupIds) ?: null,
            'unitGroupTypes' => implode(',', $this->unitGroupTypes) ?: null,
            'channelCode' => $this->channelCode?->value,
            'promoCode' => $this->promoCode,
            'corporateCode' => $this->corporateCode,
            'childrenAges' => implode(',', $this->childrenAges) ?: null,
            'includeUnavailable' => $this->includeUnavailable,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
