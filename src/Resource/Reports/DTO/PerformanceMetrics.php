<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Reports\DTO;

use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

/**
 * The counts/revenue block apaleo repeats identically at the property level, per business day, and
 * per unit group within PropertyPerformanceReportModel — extracted once instead of tripled.
 */
final readonly class PerformanceMetrics
{
    public function __construct(
        public int $houseCount,
        public int $houseItemsCount,
        public int $soldCount,
        public int $soldItemsCount,
        public int $unsoldCount,
        public int $unsoldItemsCount,
        public int $outOfOrderCount,
        public int $outOfOrderItemsCount,
        public int $tentativelyBlockedCount,
        public int $tentativelyBlockedItemsCount,
        public int $definitelyBlockedCount,
        public int $optionallyBlockedCount,
        public int $arrivalsCount,
        public int $departuresCount,
        public int $noShowsCount,
        public int $cancellationsCount,
        public float $occupancyPercentage,
        public MonetaryValue $grossUnitRevenue,
        public MonetaryValue $netUnitRevenue,
        public MonetaryValue $grossAccommodationRevenue,
        public MonetaryValue $netAccommodationRevenue,
        public MonetaryValue $grossFoodAndBeveragesRevenue,
        public MonetaryValue $netFoodAndBeveragesRevenue,
        public MonetaryValue $grossOtherRevenue,
        public MonetaryValue $netOtherRevenue,
        public MonetaryValue $grossAdr,
        public MonetaryValue $netAdr,
        public MonetaryValue $revPar,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            houseCount: ResponseData::int($data, 'houseCount'),
            houseItemsCount: ResponseData::int($data, 'houseItemsCount'),
            soldCount: ResponseData::int($data, 'soldCount'),
            soldItemsCount: ResponseData::int($data, 'soldItemsCount'),
            unsoldCount: ResponseData::int($data, 'unsoldCount'),
            unsoldItemsCount: ResponseData::int($data, 'unsoldItemsCount'),
            outOfOrderCount: ResponseData::int($data, 'outOfOrderCount'),
            outOfOrderItemsCount: ResponseData::int($data, 'outOfOrderItemsCount'),
            tentativelyBlockedCount: ResponseData::int($data, 'tentativelyBlockedCount'),
            tentativelyBlockedItemsCount: ResponseData::int($data, 'tentativelyBlockedItemsCount'),
            definitelyBlockedCount: ResponseData::int($data, 'definitelyBlockedCount'),
            optionallyBlockedCount: ResponseData::int($data, 'optionallyBlockedCount'),
            arrivalsCount: ResponseData::int($data, 'arrivalsCount'),
            departuresCount: ResponseData::int($data, 'departuresCount'),
            noShowsCount: ResponseData::int($data, 'noShowsCount'),
            cancellationsCount: ResponseData::int($data, 'cancellationsCount'),
            occupancyPercentage: ResponseData::float($data, 'occupancyPercentage'),
            grossUnitRevenue: MonetaryValue::fromArray(ResponseData::nested($data, 'grossUnitRevenue')),
            netUnitRevenue: MonetaryValue::fromArray(ResponseData::nested($data, 'netUnitRevenue')),
            grossAccommodationRevenue: MonetaryValue::fromArray(ResponseData::nested($data, 'grossAccommodationRevenue')),
            netAccommodationRevenue: MonetaryValue::fromArray(ResponseData::nested($data, 'netAccommodationRevenue')),
            grossFoodAndBeveragesRevenue: MonetaryValue::fromArray(ResponseData::nested($data, 'grossFoodAndBeveragesRevenue')),
            netFoodAndBeveragesRevenue: MonetaryValue::fromArray(ResponseData::nested($data, 'netFoodAndBeveragesRevenue')),
            grossOtherRevenue: MonetaryValue::fromArray(ResponseData::nested($data, 'grossOtherRevenue')),
            netOtherRevenue: MonetaryValue::fromArray(ResponseData::nested($data, 'netOtherRevenue')),
            grossAdr: MonetaryValue::fromArray(ResponseData::nested($data, 'grossAdr')),
            netAdr: MonetaryValue::fromArray(ResponseData::nested($data, 'netAdr')),
            revPar: MonetaryValue::fromArray(ResponseData::nested($data, 'revPar')),
        );
    }
}
