<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Block\Enum\BlockStatus;
use Oleksyuk\Apaleo\Resource\Booking\Block\Enum\OptionalCutoffBehavior;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;

final readonly class ReplaceBlock
{
    /** @param list<CreateBlockTimeSlice> $timeSlices */
    public function __construct(
        public string $from,
        public string $to,
        public MonetaryValue $grossDailyRate,
        public array $timeSlices,
        public ?string $marketSegmentId = null,
        public ?BlockStatus $status = null,
        public ?string $optionalCutoff = null,
        public ?bool $isOptionalDeductingInventory = null,
        public ?OptionalCutoffBehavior $optionalCutoffBehavior = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'from' => $this->from,
            'to' => $this->to,
            'grossDailyRate' => $this->grossDailyRate->toArray(),
            'timeSlices' => array_map(static fn (CreateBlockTimeSlice $s): array => $s->toArray(), $this->timeSlices),
            'marketSegmentId' => $this->marketSegmentId,
            'status' => $this->status?->value,
            'optionalCutoff' => $this->optionalCutoff,
            'isOptionalDeductingInventory' => $this->isOptionalDeductingInventory,
            'optionalCutoffBehavior' => $this->optionalCutoffBehavior?->value,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
