<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

/** A window in which the rate plan can be booked. */
final readonly class BookingPeriod
{
    public function __construct(
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'from' => $this->from->format(\DateTimeInterface::ATOM),
            'to' => $this->to->format(\DateTimeInterface::ATOM),
        ];
    }
}
