<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Account\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class ChartOfAccounts
{
    /**
     * @param list<FinanceAccountListItem> $globalAccounts
     * @param list<FinanceAccountListItem> $guestAccounts
     * @param list<FinanceAccountListItem> $externalAccounts
     * @param list<FinanceAccountListItem> $bookingAccounts
     */
    public function __construct(
        public array $globalAccounts,
        public array $guestAccounts,
        public array $externalAccounts,
        public array $bookingAccounts,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            globalAccounts: array_map(FinanceAccountListItem::fromArray(...), ResponseData::nestedList($data, 'globalAccounts')),
            guestAccounts: array_map(FinanceAccountListItem::fromArray(...), ResponseData::nestedList($data, 'guestAccounts')),
            externalAccounts: array_map(FinanceAccountListItem::fromArray(...), ResponseData::nestedList($data, 'externalAccounts')),
            bookingAccounts: array_map(FinanceAccountListItem::fromArray(...), ResponseData::nestedList($data, 'bookingAccounts')),
        );
    }
}
