<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Action;
use Oleksyuk\Apaleo\Resource\RatePlan\RatePlan\Enum\TimeSliceTemplate;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class TimeSliceDefinition
{
    /**
     * @param string $checkInTime  local time, e.g. "17:00:00"
     * @param string $checkOutTime local time, e.g. "11:00:00"
     * @param bool $isUsed used definitions can't be deleted or have their times changed
     * @param list<Action> $actions only filled with expand: ['actions']
     */
    public function __construct(
        public string $id,
        public string $name,
        public TimeSliceTemplate $template,
        public string $checkInTime,
        public string $checkOutTime,
        public bool $isUsed,
        public array $actions,
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
            isUsed: ResponseData::bool($data, 'isUsed'),
            actions: array_map(Action::fromArray(...), ResponseData::nestedList($data, 'actions')),
        );
    }
}
