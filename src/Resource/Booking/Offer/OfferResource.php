<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\ServiceOffers;
use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\StayOffers;
use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\TimeSlices;
use Oleksyuk\Apaleo\Resource\Booking\Offer\Requests\GetOffersRequest;
use Oleksyuk\Apaleo\Resource\Booking\Offer\Requests\GetRatePlanOffersRequest;
use Oleksyuk\Apaleo\Resource\Booking\Offer\Requests\GetServiceOffersRequest;
use Oleksyuk\Apaleo\Resource\Booking\Offer\Requests\OfferIndexRequest;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;

final readonly class OfferResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** A rate plan's offers across every time slice in [$from, $to) — no property/adults/etc needed, just the rate plan. */
    public function index(string $ratePlanId, \DateTimeImmutable $from, \DateTimeImmutable $to, ChannelCode $channelCode, ?int $pageNumber = null, ?int $pageSize = null): TimeSlices
    {
        $data = $this->pipeline->send(new OfferIndexRequest($ratePlanId, $from, $to, $channelCode, $pageNumber, $pageSize));

        return TimeSlices::fromArray($data);
    }

    /**
     * Bookable stay offers for a property, across every eligible rate plan/unit group.
     *
     * @param list<string> $timeSliceDefinitionIds
     * @param list<string> $unitGroupIds
     * @param list<string> $unitGroupTypes
     * @param list<int>    $childrenAges
     */
    public function forProperty(
        string $propertyId,
        string $arrival,
        string $departure,
        int $adults,
        ?string $timeSliceTemplate = null,
        array $timeSliceDefinitionIds = [],
        array $unitGroupIds = [],
        array $unitGroupTypes = [],
        ?ChannelCode $channelCode = null,
        ?string $promoCode = null,
        ?string $corporateCode = null,
        array $childrenAges = [],
        ?bool $includeUnavailable = null,
    ): StayOffers {
        $data = $this->pipeline->send(new GetOffersRequest(
            $propertyId,
            $arrival,
            $departure,
            $adults,
            $timeSliceTemplate,
            $timeSliceDefinitionIds,
            $unitGroupIds,
            $unitGroupTypes,
            $channelCode,
            $promoCode,
            $corporateCode,
            $childrenAges,
            $includeUnavailable,
        ));

        return StayOffers::fromArray($data);
    }

    /**
     * Bookable stay offers for one specific rate plan.
     *
     * @param list<int>   $childrenAges
     * @param list<float> $overridePrices desired price per time slice, to quote a custom rate
     */
    public function forRatePlan(
        string $ratePlanId,
        string $arrival,
        string $departure,
        int $adults,
        ?ChannelCode $channelCode = null,
        array $childrenAges = [],
        ?bool $includeUnavailable = null,
        array $overridePrices = [],
    ): StayOffers {
        $data = $this->pipeline->send(new GetRatePlanOffersRequest($ratePlanId, $arrival, $departure, $adults, $channelCode, $childrenAges, $includeUnavailable, $overridePrices));

        return StayOffers::fromArray($data);
    }

    /**
     * Bookable extra services for a stay under a given rate plan.
     *
     * @param list<int> $childrenAges
     */
    public function services(
        string $ratePlanId,
        string $arrival,
        string $departure,
        int $adults,
        ?ChannelCode $channelCode = null,
        array $childrenAges = [],
        ?bool $onlyDefaultDates = null,
        ?bool $includeUnavailable = null,
    ): ServiceOffers {
        $data = $this->pipeline->send(new GetServiceOffersRequest($ratePlanId, $arrival, $departure, $adults, $channelCode, $childrenAges, $onlyDefaultDates, $includeUnavailable));

        return ServiceOffers::fromArray($data);
    }
}
