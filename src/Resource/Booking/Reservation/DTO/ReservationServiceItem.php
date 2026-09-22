<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Service;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ReservationServiceItem
{
    /** @param list<ServiceDateItem> $dates */
    public function __construct(
        public Service $service,
        public Amount $totalAmount,
        public array $dates,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            service: Service::fromArray(ResponseData::nested($data, 'service')),
            totalAmount: Amount::fromArray(ResponseData::nested($data, 'totalAmount')),
            dates: array_map(ServiceDateItem::fromArray(...), ResponseData::nestedList($data, 'dates')),
        );
    }
}
