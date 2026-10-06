<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
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
            account: ResponseData::requiredNested($data, 'account', ExportAccount::fromArray(...)),
            netAmount: ResponseData::requiredNested($data, 'netAmount', MonetaryValue::fromArray(...)),
            grossAmount: ResponseData::requiredNested($data, 'grossAmount', MonetaryValue::fromArray(...)),
            children: ResponseData::mapList($data, 'children', self::fromArray(...)),
        );
    }
}
