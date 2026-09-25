<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\UnitGroup\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\JsonPatch;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TimeSliceTemplate;

final readonly class UpdateUnitGroupAvailabilityRequest extends Request
{
    public function __construct(
        private string $unitGroupId,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to,
        private TimeSliceTemplate $timeSliceTemplate,
        private JsonPatch $patch,
    ) {}

    public function method(): Method
    {
        return Method::PATCH;
    }

    public function endpoint(): string
    {
        return '/availability/v1/unit-groups/'.rawurlencode($this->unitGroupId);
    }

    public function query(): array
    {
        return [
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'timeSliceTemplate' => $this->timeSliceTemplate->value,
        ];
    }

    public function body(): array
    {
        return $this->patch->toArray();
    }
}
