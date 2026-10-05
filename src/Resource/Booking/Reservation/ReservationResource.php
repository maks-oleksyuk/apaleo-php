<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Offer\DTO\ServiceOffers;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\AutoAssignedUnitItem;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\BookReservationService;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\DesiredStayDetails;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\Reservation;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\ReservationListItem;
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
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitCondition;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ReservationResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @param list<'actions'|'assignedUnits'|'booker'|'company'|'services'|'timeSlices'> $expand
     *
     * @throws ApaleoExceptionInterface
     */
    public function get(string $reservationId, array $expand = []): Reservation
    {
        $data = $this->pipeline->send(new GetReservationRequest($reservationId, $expand));

        return Reservation::fromArray($data);
    }

    /**
     * @param list<string> $sort
     * @param list<'actions'|'assignedUnits'|'booker'|'company'|'services'|'timeSlices'> $expand
     *
     * @return PaginatedResult<ReservationListItem>
     *
     * @throws ApaleoExceptionInterface
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

        return PaginatedResult::fromResponse($data, 'reservations', ReservationListItem::fromArray(...));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function count(ReservationFilter $filter = new ReservationFilter()): int
    {
        $data = $this->pipeline->send(new CountReservationsRequest($filter));

        return ResponseData::int($data, 'count');
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function update(string $reservationId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateReservationRequest($reservationId, $patch));
    }

    /**
     * @return list<ReservationServiceItem>
     *
     * @throws ApaleoExceptionInterface
     */
    public function services(string $reservationId): array
    {
        $data = $this->pipeline->send(new GetReservationServicesRequest($reservationId));

        return array_map(ReservationServiceItem::fromArray(...), ResponseData::nestedList($data, 'services'));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function removeService(string $reservationId, string $serviceId): void
    {
        $this->pipeline->send(new DeleteReservationServiceRequest($reservationId, $serviceId));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function bookService(string $reservationId, BookReservationService $service, bool $force = false): void
    {
        $this->pipeline->send(new BookReservationServiceRequest($reservationId, $service, $force));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function checkIn(string $reservationId, ?bool $withCityTax = null): void
    {
        $this->pipeline->send(new CheckInRequest($reservationId, $withCityTax));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function checkOut(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'checkout'));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function cancel(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'cancel'));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function noShow(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'noshow'));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function revertCheckIn(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'revert-checkin'));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function addCityTax(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'add-city-tax'));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function removeCityTax(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'remove-city-tax'));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function lockUnit(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'lock-unit'));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function unlockUnit(string $reservationId): void
    {
        $this->pipeline->send(new ReservationSimpleActionRequest($reservationId, 'unlock-unit'));
    }

    /**
     * @throws ApaleoExceptionInterface
     */
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
     *
     * @throws ApaleoExceptionInterface
     */
    public function assignUnit(string $reservationId, array $unitConditions = []): array
    {
        $data = $this->pipeline->send(new AssignUnitRequest($reservationId, $unitConditions));

        return array_map(AutoAssignedUnitItem::fromArray(...), ResponseData::nestedList($data, 'timeSlices'));
    }

    /**
     * Assigns a specific unit, optionally only for part of the stay.
     *
     * @throws ApaleoExceptionInterface
     */
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

    /**
     * @throws ApaleoExceptionInterface
     */
    public function amend(string $reservationId, DesiredStayDetails $details, bool $force = false): void
    {
        $this->pipeline->send(new AmendReservationRequest($reservationId, $details, $force));
    }

    /**
     * Offers for amending this reservation's stay (arrival/departure/adults/rate plan).
     *
     * @param list<int>    $childrenAges
     * @param list<string> $unitGroupIds
     *
     * @throws ApaleoExceptionInterface
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

    /**
     * @throws ApaleoExceptionInterface
     */
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
