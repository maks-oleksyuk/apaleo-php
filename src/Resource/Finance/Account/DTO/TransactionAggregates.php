<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class TransactionAggregates
{
    /** @param list<AccountAggregate> $aggregations one per account */
    public function __construct(
        public array $aggregations,
        public AccountAggregate $total,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            aggregations: array_map(AccountAggregate::fromArray(...), ResponseData::nestedList($data, 'aggregations')),
            total: AccountAggregate::fromArray(ResponseData::nested($data, 'total')),
        );
    }
}
