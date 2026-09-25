<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio\DTO;

use Oleksyuk\Apaleo\Resource\RatePlan\Company\DTO\InvoiceNetworkIdentity;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CompanyInfo
{
    public function __construct(
        public string $name,
        public ?string $taxId = null,
        public ?string $additionalTaxId = null,
        public ?string $additionalTaxId2 = null,
        public ?InvoiceNetworkIdentity $invoiceNetworkIdentity = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            name: ResponseData::string($data, 'name'),
            taxId: ResponseData::nullableString($data, 'taxId'),
            additionalTaxId: ResponseData::nullableString($data, 'additionalTaxId'),
            additionalTaxId2: ResponseData::nullableString($data, 'additionalTaxId2'),
            invoiceNetworkIdentity: ResponseData::nullableNested($data, 'invoiceNetworkIdentity', InvoiceNetworkIdentity::fromArray(...)),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'taxId' => $this->taxId,
            'additionalTaxId' => $this->additionalTaxId,
            'additionalTaxId2' => $this->additionalTaxId2,
            'invoiceNetworkIdentity' => $this->invoiceNetworkIdentity?->toArray(),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
