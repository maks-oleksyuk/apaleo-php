<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ServiceOffers
{
    /** @param list<ServiceOffer> $services */
    public function __construct(
        public array $services,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            services: array_map(ServiceOffer::fromArray(...), ResponseData::nestedList($data, 'services')),
        );
    }
}
