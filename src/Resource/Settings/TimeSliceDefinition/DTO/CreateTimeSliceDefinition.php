<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\TimeSliceDefinition\DTO;

use Oleksyuk\Apaleo\Resource\Shared\Enum\TimeSliceTemplate;

final readonly class CreateTimeSliceDefinition
{
    /**
     * @param string $checkInTime  local time, e.g. "17:00:00"
     * @param string $checkOutTime local time, e.g. "11:00:00"
     */
    public function __construct(
        public string $name,
        public TimeSliceTemplate $template,
        public string $checkInTime,
        public string $checkOutTime,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'template' => $this->template->value,
            'checkInTime' => $this->checkInTime,
            'checkOutTime' => $this->checkOutTime,
        ];
    }
}
