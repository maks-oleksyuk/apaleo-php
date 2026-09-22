<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\ServiceOffers;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\AutoAssignedUnitItem;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\BookReservationService;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\DesiredStayDetails;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\Reservation;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\ReservationServiceItem;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\ReservationStayOffers;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\AmendReservationRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\AssignSpecificUnitRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\AssignUnitRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\BookReservationServiceRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\CheckInRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\CountReservationsRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\DeleteReservationServiceRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\GetReservationOffersRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\GetReservationRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\GetReservationServiceOffersRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\GetReservationServicesRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\ListReservationsRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\ReservationSimpleActionRequest;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Requests\UpdateReservationRequest;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedUnit;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\UnitCondition;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ReservationResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @param list<'actions'|'assignedUnits'|'booker'|'company'|'services'|'timeSlices'> $expand */
    public function get(string $reservationId, array $expand = []): Reservation
    {
        $data = $this->pipeline->send(new GetReservationRequest($reservationId, $expand));

        return Reservation::fromArray($data);
    }

    /**
     * @param list<string> $sort
     * @param list<'actions'|'assignedUnits'|'booker'|'company'|'services'|'timeSlices'> $expand
     *
     * @return PaginatedResult<Reservation>
     */
    public function list(
        ReservationFilter $filter = new ReservationFilter(),
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $sort = [],
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListReservationsRequest($filter, $pageNumber, $pageSize, $sort, $expand));

        return new PaginatedResult(
            items: array_map(Reservation::fromArray(...), ResponseData::nestedList($data, 'reservations')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function count(ReservationFilter $filter = new ReservationFilter()): int
    {
        $data = $this->pipeline->send(new CountReservationsRequest($filter));

        return ResponseData::int($data, 'count');
    }

    public function update(string $reservationId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateReservationRequest($reservationId, $patch));
    }

    /** @return list<ReservationServiceItem> */
    public function services(string $reservationId): array
    {
        $data = $this->pipeline->send(new GetReservationServicesRequest($reservationId));

        return array_map(ReservationServiceItem::fromArray(...), ResponseData::nestedList($data, 'services'));
    }

    public function removeService(string $reservationId, string $serviceId): void
    {
        $this->pipeline->send(new DeleteReservationServiceRequest($reservationId, $serviceId));
    }

    public function bookService(string $reservationId, BookReservationService $service, bool $force = false): void
    {
        $this->pipeline->send(new BookReservationServiceRequest($reservationId, $service, $force));
    }

    public function checkIn(string $reservationId, ?bool $withCityTax = null): void
    {
        $this->pipeline->send(new CheckInRequest($reservationId, $withCityTax));
    }

    public function checkOut(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'checkout'));
    }

    public function cancel(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'cancel'));
    }

    public function noShow(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'noshow'));
    }

    public function revertCheckIn(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'revert-checkin'));
    }

    public function addCityTax(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'add-city-tax'));
    }

    public function removeCityTax(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'remove-city-tax'));
    }

    public function lockUnit(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'lock-unit'));
    }

    public function unlockUnit(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'unlock-unit'));
    }

    public function unassignUnits(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'unassign-units'));
    }

    /**
     * Auto-assigns a unit for the whole stay.
     *
     * @param list<UnitCondition> $unitConditions
     *
     * @return list<AutoAssignedUnitItem>
     */
    public function assignUnit(string $reservationId, array $unitConditions = []): array
    {
        $data = $this->pipeline->send(new AssignUnitRequest($reservationId, $unitConditions));

        return array_map(AutoAssignedUnitItem::fromArray(...), ResponseData::nestedList($data, 'timeSlices'));
    }

    /** Assigns a specific unit, optionally only for part of the stay. */
    public function assignSpecificUnit(
        string $reservationId,
        string $unitId,
        ?\DateTimeImmutable $from = null,
        ?\DateTimeImmutable $to = null,
        ?bool $lockUnit = null,
    ): EmbeddedUnit {
        $data = $this->pipeline->send(new AssignSpecificUnitRequest($reservationId, $unitId, $from, $to, $lockUnit));

        return EmbeddedUnit::fromArray(ResponseData::nested($data, 'unit'));
    }

    public function amend(string $reservationId, DesiredStayDetails $details, bool $force = false): void
    {
        $this->pipeline->send(new AmendReservationRequest($reservationId, $details, $force));
    }

    /**
     * Offers for amending this reservation's stay (arrival/departure/adults/rate plan).
     *
     * @param list<int>    $childrenAges
     * @param list<string> $unitGroupIds
     */
    public function offers(
        string $reservationId,
        ?\DateTimeImmutable $arrival = null,
        ?\DateTimeImmutable $departure = null,
        ?int $adults = null,
        array $childrenAges = [],
        ?ChannelCode $channelCode = null,
        ?string $promoCode = null,
        ?string $corporateCode = null,
        ?bool $requote = null,
        ?bool $includeUnavailable = null,
        array $unitGroupIds = [],
    ): ReservationStayOffers {
        $data = $this->pipeline->send(new GetReservationOffersRequest(
            $reservationId,
            $arrival,
            $departure,
            $adults,
            $childrenAges,
            $channelCode,
            $promoCode,
            $corporateCode,
            $requote,
            $includeUnavailable,
            $unitGroupIds,
        ));

        return ReservationStayOffers::fromArray($data);
    }

    /** Bookable extra services for this reservation. */
    public function serviceOffers(
        string $reservationId,
        ?ChannelCode $channelCode = null,
        ?bool $onlyDefaultDates = null,
        ?bool $includeUnavailable = null,
    ): ServiceOffers {
        $data = $this->pipeline->send(new GetReservationServiceOffersRequest($reservationId, $channelCode, $onlyDefaultDates, $includeUnavailable));

        return ServiceOffers::fromArray($data);
    }
}
