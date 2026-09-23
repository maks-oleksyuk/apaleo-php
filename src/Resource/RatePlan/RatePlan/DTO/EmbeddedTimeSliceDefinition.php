<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class EmbeddedTimeSliceDefinition
{
    /**
     * @param string $checkInTime  local time, e.g. "17:00:00"
     * @param string $checkOutTime local time, e.g. "11:00:00"
     */
    public function __construct(
        public string $id,
        public string $name,
        public TimeSliceTemplate $template,
        public string $checkInTime,
        public string $checkOutTime,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            name: ResponseData::string($data, 'name'),
            template: TimeSliceTemplate::fromApi(ResponseData::string($data, 'template')),
            checkInTime: ResponseData::string($data, 'checkInTime'),
            checkOutTime: ResponseData::string($data, 'checkOutTime'),
        );
    }
}
