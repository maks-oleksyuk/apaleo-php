<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Support\Query;

final readonly class ListCompanyInvoicesVatRequest extends Request
{
    /**
     * @param list<string> $companyIds
     * @param list<string> $dateFilter expressions like "gte_2024-01-01", "lt_2024-02-01" (interval capped at 1 month by the API)
     */
    public function __construct(
        private string $propertyId,
        private array $companyIds = [],
        private array $dateFilter = [],
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/reports/v1/reports/company-invoices-vat';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'companyIds' => Query::csv($this->companyIds),
            'dateFilter' => Query::csv($this->dateFilter),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
