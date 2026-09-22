<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;

final readonly class CreateBlock
{
    /** @param list<CreateBlockTimeSlice> $timeSlices */
    public function __construct(
        public string $groupId,
        public string $ratePlanId,
        public string $from,
        public string $to,
        public MonetaryValue $grossDailyRate,
        public array $timeSlices = [],
        public ?int $blockedUnits = null,
        public ?string $promoCode = null,
        public ?string $corporateCode = null,
        public ?string $marketSegmentId = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'groupId' => $this->groupId,
            'ratePlanId' => $this->ratePlanId,
            'from' => $this->from,
            'to' => $this->to,
            'grossDailyRate' => $this->grossDailyRate->toArray(),
            'timeSlices' => $this->timeSlices !== [] ? array_map(static fn (CreateBlockTimeSlice $s): array => $s->toArray(), $this->timeSlices) : null,
            'blockedUnits' => $this->blockedUnits,
            'promoCode' => $this->promoCode,
            'corporateCode' => $this->corporateCode,
            'marketSegmentId' => $this->marketSegmentId,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
