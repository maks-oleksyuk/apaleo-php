<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Logs\DTO;

use Oleksyuk\Apaleo\Resource\Logs\Enum\TransactionsExportType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class TransactionsExportLogItem
{
    public function __construct(
        public \DateTimeImmutable $periodStart,
        public \DateTimeImmutable $periodEnd,
        public TransactionsExportType $type,
        public ?string $clientId,
        public string $propertyId,
        public \DateTimeImmutable $created,
        public ?string $subjectId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            periodStart: ResponseData::dateTime($data, 'periodStart'),
            periodEnd: ResponseData::dateTime($data, 'periodEnd'),
            type: TransactionsExportType::fromApi(ResponseData::string($data, 'type')),
            clientId: ResponseData::nullableString($data, 'clientId'),
            propertyId: ResponseData::string($data, 'propertyId'),
            created: ResponseData::dateTime($data, 'created'),
            subjectId: ResponseData::nullableString($data, 'subjectId'),
        );
    }
}
