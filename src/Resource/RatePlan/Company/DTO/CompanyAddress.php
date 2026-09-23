<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Company\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CompanyAddress
{
    /** @param string $countryCode ISO 3166-1 alpha-2 */
    public function __construct(
        public string $addressLine1,
        public string $postalCode,
        public string $city,
        public string $countryCode,
        public ?string $addressLine2 = null,
        public ?string $regionCode = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            addressLine1: ResponseData::string($data, 'addressLine1'),
            postalCode: ResponseData::string($data, 'postalCode'),
            city: ResponseData::string($data, 'city'),
            countryCode: ResponseData::string($data, 'countryCode'),
            addressLine2: ResponseData::nullableString($data, 'addressLine2'),
            regionCode: ResponseData::nullableString($data, 'regionCode'),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'addressLine1' => $this->addressLine1,
            'addressLine2' => $this->addressLine2,
            'postalCode' => $this->postalCode,
            'city' => $this->city,
            'regionCode' => $this->regionCode,
            'countryCode' => $this->countryCode,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
