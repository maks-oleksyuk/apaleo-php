<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Account\DTO;

use Oleksyuk\Apaleo\Resource\Account\Enum\AccountType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class AccountListItem
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public AccountType $type,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::nullableString($data, 'description'),
            type: AccountType::fromApi(ResponseData::string($data, 'type')),
        );
    }
}
