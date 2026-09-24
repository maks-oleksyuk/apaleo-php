<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\PersonAddress;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class InvoiceRecipient
{
    public function __construct(
        public ?string $name,
        public ?PersonAddress $address,
        public ?string $companyName,
        public ?string $companyTaxId,
        public ?string $reference,
        public ?string $personalTaxId,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $address = ResponseData::nested($data, 'address');

        return new self(
            name: ResponseData::nullableString($data, 'name'),
            address: $address !== [] ? PersonAddress::fromArray($address) : null,
            companyName: ResponseData::nullableString($data, 'companyName'),
            companyTaxId: ResponseData::nullableString($data, 'companyTaxId'),
            reference: ResponseData::nullableString($data, 'reference'),
            personalTaxId: ResponseData::nullableString($data, 'personalTaxId'),
        );
    }
}
