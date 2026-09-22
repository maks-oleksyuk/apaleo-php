<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\DTO;

use Oleksyuk\Apaleo\Resource\Logs\Enum\ReservationLogEventType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ReservationChangeLogItem
{
    /** @param list<ReservationChange> $changes populated only when the request expands "changes" */
    public function __construct(
        public string $reservationId,
        public ReservationLogEventType $eventType,
        public array $changes,
        public string $clientId,
        public string $propertyId,
        public \DateTimeImmutable $created,
        public ?string $subjectId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            reservationId: ResponseData::string($data, 'reservationId'),
            eventType: ReservationLogEventType::fromApi(ResponseData::string($data, 'eventType')),
            changes: array_map(ReservationChange::fromArray(...), ResponseData::nestedList($data, 'changes')),
            clientId: ResponseData::string($data, 'clientId'),
            propertyId: ResponseData::string($data, 'propertyId'),
            created: ResponseData::dateTime($data, 'created'),
            subjectId: ResponseData::nullableString($data, 'subjectId'),
        );
    }
}
