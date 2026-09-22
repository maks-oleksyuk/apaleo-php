<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Offer\Enum\PricingMode;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedService;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ReservationStayOfferService
{
    public function __construct(
        public EmbeddedService $service,
        public \DateTimeImmutable $serviceDate,
        public int $count,
        public ?int $availableCount,
        public Amount $amount,
        public bool $bookedAsExtra,
        public PricingMode $pricingMode,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            service: EmbeddedService::fromArray(ResponseData::nested($data, 'service')),
            serviceDate: ResponseData::dateTime($data, 'serviceDate'),
            count: ResponseData::int($data, 'count'),
            availableCount: ResponseData::nullableInt($data, 'availableCount'),
            amount: Amount::fromArray(ResponseData::nested($data, 'amount')),
            bookedAsExtra: ResponseData::bool($data, 'bookedAsExtra'),
            pricingMode: PricingMode::fromApi(ResponseData::string($data, 'pricingMode')),
        );
    }
}
