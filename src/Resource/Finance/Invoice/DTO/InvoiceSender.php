<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO;

use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\Address;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class InvoiceSender
{
    public function __construct(
        public string $name,
        public ?Address $address,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $address = ResponseData::nested($data, 'address');

        return new self(
            name: ResponseData::string($data, 'name'),
            address: $address !== [] ? Address::fromArray($address) : null,
        );
    }
}
