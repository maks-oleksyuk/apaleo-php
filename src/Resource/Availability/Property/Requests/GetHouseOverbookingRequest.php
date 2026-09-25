<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Property\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;

final readonly class GetHouseOverbookingRequest extends Request
{
    public function __construct(
        private string $propertyId,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to,
        private TimeSliceTemplate $timeSliceTemplate,
        private UnitGroupType $unitGroupType,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/availability/v1/properties/'.rawurlencode($this->propertyId);
    }

    public function query(): array
    {
        return [
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'timeSliceTemplate' => $this->timeSliceTemplate->value,
            'unitGroupType' => $this->unitGroupType->value,
        ];
    }
}
