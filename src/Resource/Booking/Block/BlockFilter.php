<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block;

use Oleksyuk\Apaleo\Resource\Booking\Block\Enum\BlockStatus;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\Query;

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
            'propertyIds' => Query::csv($this->propertyIds),
            'status' => Query::csv($this->status),
            'unitGroupIds' => Query::csv($this->unitGroupIds),
            'ratePlanIds' => Query::csv($this->ratePlanIds),
            'timeSliceDefinitionIds' => Query::csv($this->timeSliceDefinitionIds),
            'unitGroupTypes' => Query::csv($this->unitGroupTypes),
            'timeSliceTemplate' => $this->timeSliceTemplate?->value,
            'from' => $this->from?->format(\DateTimeInterface::ATOM),
            'to' => $this->to?->format(\DateTimeInterface::ATOM),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
