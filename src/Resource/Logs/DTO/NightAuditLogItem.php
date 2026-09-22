<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\DTO;

use Oleksyuk\Apaleo\Resource\Logs\Enum\NightAuditFailureCode;
use Oleksyuk\Apaleo\Resource\Logs\Enum\NightAuditStatus;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class NightAuditLogItem
{
    /** @param list<string> $reservationIdsSetToNoShow */
    public function __construct(
        public \DateTimeImmutable $ended,
        public bool $setReservationsToNoShow,
        public NightAuditStatus $status,
        public ?NightAuditFailureCode $failureCode,
        public ?string $failureReason,
        public array $reservationIdsSetToNoShow,
        public string $propertyId,
        public \DateTimeImmutable $created,
        public ?string $subjectId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $failureCode = ResponseData::nullableString($data, 'failureCode');

        return new self(
            ended: ResponseData::dateTime($data, 'ended'),
            setReservationsToNoShow: ResponseData::bool($data, 'setReservationsToNoShow'),
            status: NightAuditStatus::fromApi(ResponseData::string($data, 'status')),
            failureCode: null !== $failureCode ? NightAuditFailureCode::fromApi($failureCode) : null,
            failureReason: ResponseData::nullableString($data, 'failureReason'),
            reservationIdsSetToNoShow: ResponseData::stringListOrEmpty($data, 'reservationIdsSetToNoShow'),
            propertyId: ResponseData::string($data, 'propertyId'),
            created: ResponseData::dateTime($data, 'created'),
            subjectId: ResponseData::nullableString($data, 'subjectId'),
        );
    }
}
