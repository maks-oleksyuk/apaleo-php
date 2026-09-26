<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\UnitGroup\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\Query;

final readonly class ListUnitGroupAvailabilityRequest extends Request
{
    /**
     * @param list<UnitGroupType> $unitGroupTypes
     * @param list<string>        $timeSliceDefinitionIds
     * @param list<string>        $unitGroupIds
     * @param list<int>           $childrenAges
     */
    public function __construct(
        private string $propertyId,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to,
        private ?TimeSliceTemplate $timeSliceTemplate = null,
        private array $unitGroupTypes = [],
        private array $timeSliceDefinitionIds = [],
        private array $unitGroupIds = [],
        private ?int $adults = null,
        private array $childrenAges = [],
        private ?bool $onlySellable = null,
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/availability/v1/unit-groups';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'timeSliceTemplate' => $this->timeSliceTemplate?->value,
            'unitGroupTypes' => Query::csv($this->unitGroupTypes),
            'timeSliceDefinitionIds' => Query::csv($this->timeSliceDefinitionIds),
            'unitGroupIds' => Query::csv($this->unitGroupIds),
            'adults' => $this->adults,
            'childrenAges' => Query::csv($this->childrenAges),
            'onlySellable' => $this->onlySellable,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
