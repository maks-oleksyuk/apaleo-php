<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block;

use Oleksyuk\Apaleo\Resource\Booking\Block\Enum\BlockStatus;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\UnitGroupType;

/** Filter criteria shared by BlockResource::list() and ::count(). */
final readonly class BlockFilter
{
    /**
     * @param list<string>        $propertyIds
     * @param list<BlockStatus>   $status
     * @param list<string>        $unitGroupIds
     * @param list<string>        $ratePlanIds
     * @param list<string>        $timeSliceDefinitionIds
     * @param list<UnitGroupType> $unitGroupTypes
     */
    public function __construct(
        public ?string $groupId = null,
        public array $propertyIds = [],
        public array $status = [],
        public array $unitGroupIds = [],
        public array $ratePlanIds = [],
        public array $timeSliceDefinitionIds = [],
        public array $unitGroupTypes = [],
        public ?TimeSliceTemplate $timeSliceTemplate = null,
        public ?\DateTimeImmutable $from = null,
        public ?\DateTimeImmutable $to = null,
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'groupId' => $this->groupId,
            'propertyIds' => implode(',', $this->propertyIds) ?: null,
            'status' => implode(',', array_map(static fn (BlockStatus $s): string => $s->value, $this->status)) ?: null,
            'unitGroupIds' => implode(',', $this->unitGroupIds) ?: null,
            'ratePlanIds' => implode(',', $this->ratePlanIds) ?: null,
            'timeSliceDefinitionIds' => implode(',', $this->timeSliceDefinitionIds) ?: null,
            'unitGroupTypes' => implode(',', array_map(static fn (UnitGroupType $t): string => $t->value, $this->unitGroupTypes)) ?: null,
            'timeSliceTemplate' => $this->timeSliceTemplate?->value,
            'from' => $this->from?->format(\DateTimeInterface::ATOM),
            'to' => $this->to?->format(\DateTimeInterface::ATOM),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
