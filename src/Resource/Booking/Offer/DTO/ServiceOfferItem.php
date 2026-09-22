<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ServiceOfferItem
{
    public function __construct(
        public \DateTimeImmutable $serviceDate,
        public Amount $amount,
        public bool $isDefaultDate,
        public bool $isMandatory,
        public ?int $availableCount,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            serviceDate: ResponseData::date($data, 'serviceDate'),
            amount: Amount::fromArray(ResponseData::nested($data, 'amount')),
            isDefaultDate: ResponseData::bool($data, 'isDefaultDate'),
            isMandatory: ResponseData::bool($data, 'isMandatory'),
            availableCount: ResponseData::nullableInt($data, 'availableCount'),
        );
    }
}
