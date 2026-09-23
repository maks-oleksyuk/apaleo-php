<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Company\DTO;

final readonly class CreateCompany
{
    /** @param list<CompanyRatePlan> $ratePlans */
    public function __construct(
        public string $code,
        public string $propertyId,
        public string $name,
        public CompanyAddress $address,
        public bool $canCheckOutOnAr,
        public ?string $invoicingEmail = null,
        public ?string $phone = null,
        public ?string $taxId = null,
        public ?string $additionalTaxId = null,
        public ?string $additionalTaxId2 = null,
        public ?InvoiceNetworkIdentity $invoiceNetworkIdentity = null,
        public array $ratePlans = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'code' => $this->code,
            'propertyId' => $this->propertyId,
            'name' => $this->name,
            'invoicingEmail' => $this->invoicingEmail,
            'phone' => $this->phone,
            'taxId' => $this->taxId,
            'additionalTaxId' => $this->additionalTaxId,
            'additionalTaxId2' => $this->additionalTaxId2,
            'invoiceNetworkIdentity' => $this->invoiceNetworkIdentity?->toArray(),
            'address' => $this->address->toArray(),
            'canCheckOutOnAr' => $this->canCheckOutOnAr,
            'ratePlans' => array_map(static fn (CompanyRatePlan $r): array => $r->toArray(), $this->ratePlans) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
