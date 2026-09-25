<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\RatePlan\Company\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Company
{
    /** @param list<CompanyRatePlan> $ratePlans */
    public function __construct(
        public string $id,
        public string $code,
        public string $propertyId,
        public string $name,
        public CompanyAddress $address,
        public bool $canCheckOutOnAr,
        public ?string $invoicingEmail,
        public ?string $phone,
        public ?string $taxId,
        public ?string $additionalTaxId,
        public ?string $additionalTaxId2,
        public ?InvoiceNetworkIdentity $invoiceNetworkIdentity,
        public array $ratePlans,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: ResponseData::string($data, 'id'),
            code: ResponseData::string($data, 'code'),
            propertyId: ResponseData::string($data, 'propertyId'),
            name: ResponseData::string($data, 'name'),
            address: CompanyAddress::fromArray(ResponseData::nested($data, 'address')),
            canCheckOutOnAr: ResponseData::bool($data, 'canCheckOutOnAr'),
            invoicingEmail: ResponseData::nullableString($data, 'invoicingEmail'),
            phone: ResponseData::nullableString($data, 'phone'),
            taxId: ResponseData::nullableString($data, 'taxId'),
            additionalTaxId: ResponseData::nullableString($data, 'additionalTaxId'),
            additionalTaxId2: ResponseData::nullableString($data, 'additionalTaxId2'),
            invoiceNetworkIdentity: ResponseData::nullableNested($data, 'invoiceNetworkIdentity', InvoiceNetworkIdentity::fromArray(...)),
            ratePlans: array_map(CompanyRatePlan::fromArray(...), ResponseData::nestedList($data, 'ratePlans')),
        );
    }
}
