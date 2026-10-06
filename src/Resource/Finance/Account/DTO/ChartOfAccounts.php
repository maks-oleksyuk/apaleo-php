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
            globalAccounts: ResponseData::mapList($data, 'globalAccounts', FinanceAccountListItem::fromArray(...)),
            guestAccounts: ResponseData::mapList($data, 'guestAccounts', FinanceAccountListItem::fromArray(...)),
            externalAccounts: ResponseData::mapList($data, 'externalAccounts', FinanceAccountListItem::fromArray(...)),
            bookingAccounts: ResponseData::mapList($data, 'bookingAccounts', FinanceAccountListItem::fromArray(...)),
        );
    }
}
