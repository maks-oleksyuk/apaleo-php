<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Amount;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class TimeSliceChange
{
    public function __construct(
        public ?string $ratePlanId,
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public ?string $unitId,
        public ?Amount $amount,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $amount = ResponseData::nested($data, 'amount');

        return new self(
            ratePlanId: ResponseData::nullableString($data, 'ratePlanId'),
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            unitId: ResponseData::nullableString($data, 'unitId'),
            amount: [] !== $amount ? Amount::fromArray($amount) : null,
        );
    }
}
