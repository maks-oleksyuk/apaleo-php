<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\Requests;

use Oleksyuk\Apaleo\Http\Enum\Method;
use Oleksyuk\Apaleo\Http\Request;
use Oleksyuk\Apaleo\Resource\Finance\Account\Enum\AccountingSchema;

final readonly class GetFinanceAccountRequest extends Request
{
    public function __construct(
        private string $propertyId,
        private string $accountNumber,
        private ?int $transactionLimit = null,
        private ?bool $includeArchived = null,
        private ?AccountingSchema $accountingSchema = null,
        private ?string $languageCode = null,
    ) {}

    public function method(): Method
    {
        return Method::GET;
    }

    public function endpoint(): string
    {
        return '/finance/v1/accounts/'.rawurlencode($this->accountNumber);
    }

    public function query(): array
    {
        return array_filter([
            'propertyId' => $this->propertyId,
            'transactionLimit' => $this->transactionLimit,
            'includeArchived' => $this->includeArchived,
            'accountingSchema' => $this->accountingSchema?->value,
            'languageCode' => $this->languageCode,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
