<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

/** Credited and debited sums over a period; $account is null on the grand total across all accounts. */
final readonly class AccountAggregate
{
    public function __construct(
        public ?ExportAccount $account,
        public MonetaryValue $creditedAmount,
        public MonetaryValue $debitedAmount,
        public MonetaryValue $balance,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $account = ResponseData::nested($data, 'account');

        return new self(
            account: $account !== [] ? ExportAccount::fromArray($account) : null,
            creditedAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'creditedAmount')),
            debitedAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'debitedAmount')),
            balance: MonetaryValue::fromArray(ResponseData::nested($data, 'balance')),
        );
    }
}
