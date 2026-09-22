<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class CompanyInvoice
{
    public function __construct(
        public Company $company,
        public Invoice $invoice,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            company: Company::fromArray(ResponseData::nested($data, 'company')),
            invoice: Invoice::fromArray(ResponseData::nested($data, 'invoice')),
        );
    }
}
