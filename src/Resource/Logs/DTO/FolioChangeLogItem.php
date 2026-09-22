<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Logs\Enum\FolioLogEventType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class FolioChangeLogItem
{
    public function __construct(
        public string $folioId,
        public FolioLogEventType $eventType,
        public ?string $relatedEntityId,
        public ?string $relatedEntityDescription,
        public ?MonetaryValue $amount,
        public string $clientId,
        public ?\DateTimeImmutable $serviceDate,
        public string $propertyId,
        public \DateTimeImmutable $created,
        public ?string $subjectId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $amount = ResponseData::nested($data, 'amount');

        return new self(
            folioId: ResponseData::string($data, 'folioId'),
            eventType: FolioLogEventType::fromApi(ResponseData::string($data, 'eventType')),
            relatedEntityId: ResponseData::nullableString($data, 'relatedEntityId'),
            relatedEntityDescription: ResponseData::nullableString($data, 'relatedEntityDescription'),
            amount: [] !== $amount ? MonetaryValue::fromArray($amount) : null,
            clientId: ResponseData::string($data, 'clientId'),
            serviceDate: ResponseData::nullableDateTime($data, 'serviceDate'),
            propertyId: ResponseData::string($data, 'propertyId'),
            created: ResponseData::dateTime($data, 'created'),
            subjectId: ResponseData::nullableString($data, 'subjectId'),
        );
    }
}
