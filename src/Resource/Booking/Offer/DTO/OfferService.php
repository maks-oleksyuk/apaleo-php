<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Offer\Enum\PricingMode;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedService;
use Oleksyuk\Apaleo\Support\ResponseData;

/** A service included in a time slice's offer (package element), as opposed to {@see ServiceOffer} (a bookable extra). */
final readonly class OfferService
{
    public function __construct(
        public EmbeddedService $service,
        public \DateTimeImmutable $serviceDate,
        public int $count,
        public ?int $availableCount,
        public Amount $amount,
        public PricingMode $pricingMode,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            service: EmbeddedService::fromArray(ResponseData::nested($data, 'service')),
            serviceDate: ResponseData::date($data, 'serviceDate'),
            count: ResponseData::int($data, 'count'),
            availableCount: ResponseData::nullableInt($data, 'availableCount'),
            amount: Amount::fromArray(ResponseData::nested($data, 'amount')),
            pricingMode: PricingMode::fromApi(ResponseData::string($data, 'pricingMode')),
        );
    }
}
