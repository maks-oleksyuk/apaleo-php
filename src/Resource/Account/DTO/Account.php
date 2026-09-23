<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Account\DTO;

use Oleksyuk\Apaleo\Resource\Account\Enum\AccountType;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\Address;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Account
{
    /** @param list<string> $additionallySupportedCountries ISO Alpha-2 country codes */
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public string $defaultLanguage,
        public ?string $logoUrl,
        public ?Address $location,
        public AccountType $type,
        public array $additionallySupportedCountries,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $location = ResponseData::nested($data, 'location');

        return new self(
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            description: ResponseData::nullableString($data, 'description'),
            defaultLanguage: ResponseData::string($data, 'defaultLanguage'),
            logoUrl: ResponseData::nullableString($data, 'logoUrl'),
            location: $location !== [] ? Address::fromArray($location) : null,
            type: AccountType::fromApi(ResponseData::string($data, 'type')),
            additionallySupportedCountries: ResponseData::stringListOrEmpty($data, 'additionallySupportedCountries'),
        );
    }
}
