<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Account\Enum\AccountingSchema;

final readonly class ListChildAccountsRequest extends Request
{
    public function __construct(
        private string $propertyId,
        private string $parent,
        private ?bool $includeArchived = null,
        private ?AccountingSchema $accountingSchema = null,
        private ?string $languageCode = null,
        private ?int $pageNumber = null,
        private ?int $pageSize = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/finance/v1/accounts/child-accounts';
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'parent' => $this->parent,
            'includeArchived' => $this->includeArchived,
            'accountingSchema' => $this->accountingSchema?->value,
            'languageCode' => $this->languageCode,
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
