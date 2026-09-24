<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\DTO;

use Oleksyuk\Apaleo\Resource\Finance\Account\Enum\AccountType;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class FinanceAccount
{
    /** @param list<AccountingTransaction> $transactions the latest ones, up to the requested transactionLimit */
    public function __construct(
        public string $accountNumber,
        public string $name,
        public AccountType $type,
        public ?string $parentNumber,
        public bool $hasChildren,
        public bool $isArchived,
        public array $transactions,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            accountNumber: ResponseData::string($data, 'accountNumber'),
            name: ResponseData::string($data, 'name'),
            type: AccountType::fromApi(ResponseData::string($data, 'type')),
            parentNumber: ResponseData::nullableString($data, 'parentNumber'),
            hasChildren: ResponseData::bool($data, 'hasChildren'),
            isArchived: ResponseData::bool($data, 'isArchived'),
            transactions: array_map(AccountingTransaction::fromArray(...), ResponseData::nestedList($data, 'transactions')),
        );
    }
}
