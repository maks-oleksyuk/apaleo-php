<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking;

use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Block\BlockResource;
use Oleksyuk\Apaleo\Resource\Booking\Booking\BookingDomainResource;
use Oleksyuk\Apaleo\Resource\Booking\Group\GroupResource;
use Oleksyuk\Apaleo\Resource\Booking\Offer\OfferResource;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\ReservationResource;
use Oleksyuk\Apaleo\Resource\Booking\Types\TypesResource;

/** Aggregates the Booking API's sub-resources (reservations, bookings, blocks, groups, offers, authorizations, payment accounts, types). */
final readonly class BookingResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    public function types(): TypesResource
    {
        return new TypesResource($this->pipeline);
    }

    public function reservations(): ReservationResource
    {
        return new ReservationResource($this->pipeline);
    }

    public function bookings(): BookingDomainResource
    {
        return new BookingDomainResource($this->pipeline);
    }

    public function blocks(): BlockResource
    {
        return new BlockResource($this->pipeline);
    }

    public function groups(): GroupResource
    {
        return new GroupResource($this->pipeline);
    }

    public function offers(): OfferResource
    {
        return new OfferResource($this->pipeline);
    }
}
