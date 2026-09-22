<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ServiceDateItem
{
    public function __construct(
        public \DateTimeImmutable $serviceDate,
        public int $count,
        public Amount $amount,
        public bool $isMandatory,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            serviceDate: ResponseData::date($data, 'serviceDate'),
            count: ResponseData::int($data, 'count'),
            amount: Amount::fromArray(ResponseData::nested($data, 'amount')),
            isMandatory: ResponseData::bool($data, 'isMandatory'),
        );
    }
}
