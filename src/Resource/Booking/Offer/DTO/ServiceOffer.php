<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Service;
use Oleksyuk\Apaleo\Support\ResponseData;

/** A bookable extra offered for a stay (or reservation); see BookReservationService to actually book it. */
final readonly class ServiceOffer
{
    /**
     * @param list<OfferFee>         $fees
     * @param list<ServiceOfferItem> $dates
     * @param list<OfferValidationMessage> $validationMessages
     */
    public function __construct(
        public Service $service,
        public int $count,
        public ?int $availableCount,
        public Amount $totalAmount,
        public MonetaryValue $prePaymentAmount,
        public array $fees,
        public array $dates,
        public array $validationMessages,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            service: Service::fromArray(ResponseData::nested($data, 'service')),
            count: ResponseData::int($data, 'count'),
            availableCount: ResponseData::nullableInt($data, 'availableCount'),
            totalAmount: Amount::fromArray(ResponseData::nested($data, 'totalAmount')),
            prePaymentAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'prePaymentAmount')),
            fees: array_map(OfferFee::fromArray(...), ResponseData::nestedList($data, 'fees')),
            dates: array_map(ServiceOfferItem::fromArray(...), ResponseData::nestedList($data, 'dates')),
            validationMessages: array_map(OfferValidationMessage::fromArray(...), ResponseData::nestedList($data, 'validationMessages')),
        );
    }
}
