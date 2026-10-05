<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking;

use Oleksyuk\Apaleo\Exception\ApaleoExceptionInterface;
use Oleksyuk\Apaleo\Http\RequestPipeline;
use Oleksyuk\Apaleo\Resource\Booking\Authorization\AuthorizationResource;
use Oleksyuk\Apaleo\Resource\Booking\Block\BlockResource;
use Oleksyuk\Apaleo\Resource\Booking\Booking\BookingDomainResource;
use Oleksyuk\Apaleo\Resource\Booking\Group\GroupResource;
use Oleksyuk\Apaleo\Resource\Booking\Offer\OfferResource;
use Oleksyuk\Apaleo\Resource\Booking\PaymentAccount\PaymentAccountResource;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\ReservationResource;
use Oleksyuk\Apaleo\Resource\Booking\Types\TypesResource;

final readonly class BookingResource
{
    public function __construct(
        private RequestPipeline $pipeline,
    ) {}

    /**
     * @throws ApaleoExceptionInterface
     */
    public function types(): TypesResource
    {
        return new TypesResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function reservations(): ReservationResource
    {
        return new ReservationResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function bookings(): BookingDomainResource
    {
        return new BookingDomainResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function blocks(): BlockResource
    {
        return new BlockResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function groups(): GroupResource
    {
        return new GroupResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function offers(): OfferResource
    {
        return new OfferResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function authorizations(): AuthorizationResource
    {
        return new AuthorizationResource($this->pipeline);
    }

    /**
     * @throws ApaleoExceptionInterface
     */
    public function paymentAccounts(): PaymentAccountResource
    {
        return new PaymentAccountResource($this->pipeline);
    }
}
