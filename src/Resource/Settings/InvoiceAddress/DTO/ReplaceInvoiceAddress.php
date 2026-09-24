<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\InvoiceAddress\DTO;

final readonly class ReplaceInvoiceAddress
{
    public function __construct(
        public string $addressLine1,
        public string $postalCode,
        public string $city,
        public string $countryCode,
        public ?string $addressLine2 = null,
        public ?string $regionCode = null,
    ) {}

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
