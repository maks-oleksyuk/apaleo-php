<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Availability\Service\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Shared\Enum\TimeSliceTemplate;

final readonly class ListServiceAvailabilityRequest extends Request
{
    /**
     * @param list<string> $timeSliceDefinitionIds
     * @param list<string> $channelCodes
     */
    public function __construct(
        private string $propertyId,
        private \DateTimeImmutable $from,
        private \DateTimeImmutable $to,
        private ?TimeSliceTemplate $timeSliceTemplate = null,
        private array $timeSliceDefinitionIds = [],
        private array $channelCodes = [],
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/availability/v1/services';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'from' => $this->from->format('Y-m-d'),
            'to' => $this->to->format('Y-m-d'),
            'timeSliceTemplate' => $this->timeSliceTemplate?->value,
            'timeSliceDefinitionIds' => implode(',', $this->timeSliceDefinitionIds) ?: null,
            'channelCodes' => implode(',', $this->channelCodes) ?: null,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
