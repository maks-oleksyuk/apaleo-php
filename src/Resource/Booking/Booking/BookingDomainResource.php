<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Booking;

use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Booking\DTO\Booking;
use Oleksyuk\Apaleo\Resource\Booking\Booking\DTO\BookingCreated;
use Oleksyuk\Apaleo\Resource\Booking\Booking\DTO\CreateBooking;
use Oleksyuk\Apaleo\Resource\Booking\Booking\DTO\ReservationsCreated;
use Oleksyuk\Apaleo\Resource\Booking\Booking\Requests\AddReservationsRequest;
use Oleksyuk\Apaleo\Resource\Booking\Booking\Requests\CreateBookingRequest;
use Oleksyuk\Apaleo\Resource\Booking\Booking\Requests\GetBookingRequest;
use Oleksyuk\Apaleo\Resource\Booking\Booking\Requests\ListBookingsRequest;
use Oleksyuk\Apaleo\Resource\Booking\Booking\Requests\UpdateBookingRequest;
use Oleksyuk\Apaleo\Resource\Booking\BookingResource;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO\CreateReservation;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Support\PaginatedResult;
use Oleksyuk\Apaleo\Support\Pagination;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Named BookingDomainResource (not BookingResource) to avoid a clash with the top-level {@see BookingResource} API aggregator. */
final readonly class BookingDomainResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /** @param list<string> $expand */
    public function get(string $bookingId, array $expand = []): Booking
    {
        $data = $this->pipeline->send(new GetBookingRequest($bookingId, $expand));

        return Booking::fromArray($data);
    }

    /**
     * @param list<string>      $bookingIds
     * @param list<ChannelCode> $channelCode
     * @param list<string>      $expand
     *
     * @return PaginatedResult<Booking>
     */
    public function list(
        ?string $reservationId = null,
        ?string $groupId = null,
        array $bookingIds = [],
        array $channelCode = [],
        ?string $externalCode = null,
        ?string $textSearch = null,
        ?bool $hasActivePaymentAccount = null,
        ?int $pageNumber = null,
        ?int $pageSize = null,
        array $expand = [],
    ): PaginatedResult {
        Pagination::assertValidPageSize($pageSize);

        $data = $this->pipeline->send(new ListBookingsRequest(
            $reservationId,
            $groupId,
            $bookingIds,
            $channelCode,
            $externalCode,
            $textSearch,
            $hasActivePaymentAccount,
            $pageNumber,
            $pageSize,
            $expand,
        ));

        return new PaginatedResult(
            items: array_map(Booking::fromArray(...), ResponseData::nestedList($data, 'bookings')),
            totalCount: ResponseData::nullableInt($data, 'count') ?? 0,
        );
    }

    public function create(CreateBooking $booking, bool $force = false, ?string $idempotencyKey = null): BookingCreated
    {
        $data = $this->pipeline->send(new CreateBookingRequest($booking, $force, $idempotencyKey));

        return BookingCreated::fromArray($data);
    }

    public function update(string $bookingId, JsonPatch $patch): void
    {
        $this->pipeline->send(new UpdateBookingRequest($bookingId, $patch));
    }

    /** @param list<CreateReservation> $reservations */
    public function addReservations(
        string $bookingId,
        array $reservations,
        bool $force = false,
        ?string $transactionReference = null,
        ?string $idempotencyKey = null,
    ): ReservationsCreated {
        $data = $this->pipeline->send(new AddReservationsRequest($bookingId, $reservations, $force, $transactionReference, $idempotencyKey));

        return ReservationsCreated::fromArray($data);
    }
}
