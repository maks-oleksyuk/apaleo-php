<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class InvoiceAddress
{
    public function __construct(
        public string $propertyId,
        public string $addressLine1,
        public ?string $addressLine2,
        public string $postalCode,
        public string $city,
        public ?string $regionCode,
        public string $countryCode,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            propertyId: ResponseData::string($data, 'propertyId'),
            addressLine1: ResponseData::string($data, 'addressLine1'),
            addressLine2: ResponseData::nullableString($data, 'addressLine2'),
            postalCode: ResponseData::string($data, 'postalCode'),
            city: ResponseData::string($data, 'city'),
            regionCode: ResponseData::nullableString($data, 'regionCode'),
            countryCode: ResponseData::string($data, 'countryCode'),
        );
    }
}
