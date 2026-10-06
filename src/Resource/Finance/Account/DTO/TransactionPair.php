<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The total booked from $creditedAccount to $debitedAccount over the requested period. */
final readonly class TransactionPair
{
    public function __construct(
        public ExportAccount $debitedAccount,
        public ExportAccount $creditedAccount,
        public MonetaryValue $amount,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            debitedAccount: ResponseData::requiredNested($data, 'debitedAccount', ExportAccount::fromArray(...)),
            creditedAccount: ResponseData::requiredNested($data, 'creditedAccount', ExportAccount::fromArray(...)),
            amount: ResponseData::requiredNested($data, 'amount', MonetaryValue::fromArray(...)),
        );
    }
}
