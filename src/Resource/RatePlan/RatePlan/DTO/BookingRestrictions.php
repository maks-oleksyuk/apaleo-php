<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Shared\DTO\Period;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class BookingRestrictions
{
    /** @param ?string $lateBookingUntil local time on the arrival day, e.g. "18:00:00" */
    public function __construct(
        public ?Period $minAdvance = null,
        public ?Period $maxAdvance = null,
        public ?string $lateBookingUntil = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $minAdvance = ResponseData::nested($data, 'minAdvance');
        $maxAdvance = ResponseData::nested($data, 'maxAdvance');

        return new self(
            minAdvance: $minAdvance !== [] ? Period::fromArray($minAdvance) : null,
            maxAdvance: $maxAdvance !== [] ? Period::fromArray($maxAdvance) : null,
            lateBookingUntil: ResponseData::nullableString($data, 'lateBookingUntil'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'minAdvance' => $this->minAdvance?->toArray(),
            'maxAdvance' => $this->maxAdvance?->toArray(),
            'lateBookingUntil' => $this->lateBookingUntil,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
