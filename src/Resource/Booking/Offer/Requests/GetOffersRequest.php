<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\UnitGroupType;

final readonly class GetOffersRequest extends Request
{
    /**
     * @param list<string> $timeSliceDefinitionIds
     * @param list<string> $unitGroupIds
     * @param list<UnitGroupType> $unitGroupTypes
     * @param list<int>    $childrenAges
     */
    public function __construct(
        private string $propertyId,
        private \DateTimeImmutable $arrival,
        private \DateTimeImmutable $departure,
        private int $adults,
        private ?TimeSliceTemplate $timeSliceTemplate = null,
        private array $timeSliceDefinitionIds = [],
        private array $unitGroupIds = [],
        private array $unitGroupTypes = [],
        private ?ChannelCode $channelCode = null,
        private ?string $promoCode = null,
        private ?string $corporateCode = null,
        private array $childrenAges = [],
        private ?bool $includeUnavailable = null,
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
            'arrival' => $this->arrival->format('Y-m-d'),
            'departure' => $this->departure->format('Y-m-d'),
            'adults' => $this->adults,
            'timeSliceTemplate' => $this->timeSliceTemplate?->value,
            'timeSliceDefinitionIds' => implode(',', $this->timeSliceDefinitionIds) ?: null,
            'unitGroupIds' => implode(',', $this->unitGroupIds) ?: null,
            'unitGroupTypes' => implode(',', array_map(static fn (UnitGroupType $t): string => $t->value, $this->unitGroupTypes)) ?: null,
            'channelCode' => $this->channelCode?->value,
            'promoCode' => $this->promoCode,
            'corporateCode' => $this->corporateCode,
            'childrenAges' => implode(',', $this->childrenAges) ?: null,
            'includeUnavailable' => $this->includeUnavailable,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
