<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class BlockTimeSlice
{
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public int $blockedUnits,
        public int $pickedUnits,
        public Amount $baseAmount,
        public MonetaryValue $totalGrossAmount,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            blockedUnits: ResponseData::int($data, 'blockedUnits'),
            pickedUnits: ResponseData::int($data, 'pickedUnits'),
            baseAmount: Amount::fromArray(ResponseData::nested($data, 'baseAmount')),
            totalGrossAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'totalGrossAmount')),
        );
    }
}
