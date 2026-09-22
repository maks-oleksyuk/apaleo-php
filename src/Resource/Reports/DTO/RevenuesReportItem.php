<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class RevenuesReportItem
{
    /** @param list<self> $children a nested account tree; leaf accounts have an empty list */
    public function __construct(
        public ExportAccount $account,
        public MonetaryValue $netAmount,
        public MonetaryValue $grossAmount,
        public array $children,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            account: ExportAccount::fromArray(ResponseData::nested($data, 'account')),
            netAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'netAmount')),
            grossAmount: MonetaryValue::fromArray(ResponseData::nested($data, 'grossAmount')),
            children: array_map(self::fromArray(...), ResponseData::nestedList($data, 'children')),
        );
    }
}
