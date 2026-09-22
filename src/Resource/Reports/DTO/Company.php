<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\Address;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Company
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public ?string $taxId,
        public Address $address,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            name: ResponseData::string($data, 'name'),
            taxId: ResponseData::nullableString($data, 'taxId'),
            address: Address::fromArray(ResponseData::nested($data, 'address')),
        );
    }
}
