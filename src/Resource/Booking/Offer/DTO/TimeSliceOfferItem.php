<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Offer\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\Enum\GuaranteeType;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedUnitGroup;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class TimeSliceOfferItem
{
    /** @param list<PerOccupancyPriceItem> $prices */
    public function __construct(
        public EmbeddedUnitGroup $unitGroup,
        public ?GuaranteeType $minGuaranteeType,
        public ?Period $minAdvance,
        public ?Period $maxAdvance,
        public int $available,
        public int $availableUnits,
        public ?RateRestrictions $restrictions,
        public array $prices,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $minGuaranteeType = ResponseData::nullableString($data, 'minGuaranteeType');

        return new self(
            unitGroup: EmbeddedUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
            minGuaranteeType: $minGuaranteeType !== null ? GuaranteeType::fromApi($minGuaranteeType) : null,
            minAdvance: ResponseData::nullableNested($data, 'minAdvance', Period::fromArray(...)),
            maxAdvance: ResponseData::nullableNested($data, 'maxAdvance', Period::fromArray(...)),
            available: ResponseData::int($data, 'available'),
            availableUnits: ResponseData::int($data, 'availableUnits'),
            restrictions: ResponseData::nullableNested($data, 'restrictions', RateRestrictions::fromArray(...)),
            prices: array_map(PerOccupancyPriceItem::fromArray(...), ResponseData::nestedList($data, 'prices')),
        );
    }
}
