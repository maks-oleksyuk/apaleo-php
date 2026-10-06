<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Service;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
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
            service: ResponseData::requiredNested($data, 'service', Service::fromArray(...)),
            count: ResponseData::int($data, 'count'),
            availableCount: ResponseData::nullableInt($data, 'availableCount'),
            totalAmount: ResponseData::requiredNested($data, 'totalAmount', Amount::fromArray(...)),
            prePaymentAmount: ResponseData::requiredNested($data, 'prePaymentAmount', MonetaryValue::fromArray(...)),
            fees: ResponseData::mapList($data, 'fees', OfferFee::fromArray(...)),
            dates: ResponseData::mapList($data, 'dates', ServiceOfferItem::fromArray(...)),
            validationMessages: ResponseData::mapList($data, 'validationMessages', OfferValidationMessage::fromArray(...)),
        );
    }
}
