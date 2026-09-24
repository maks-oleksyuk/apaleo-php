<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Account\Enum\AccountType;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The account a transaction was booked on, as referenced from exports and aggregations. */
final readonly class ExportAccount
{
    public function __construct(
        public string $number,
        public string $name,
        public AccountType $type,
        public ?string $parentNumber,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            number: ResponseData::string($data, 'number'),
            name: ResponseData::string($data, 'name'),
            type: AccountType::fromApi(ResponseData::string($data, 'type')),
            parentNumber: ResponseData::nullableString($data, 'parentNumber'),
        );
    }
}
