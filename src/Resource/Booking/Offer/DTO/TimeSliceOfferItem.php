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
        $minAdvance = ResponseData::nested($data, 'minAdvance');
        $maxAdvance = ResponseData::nested($data, 'maxAdvance');
        $restrictions = ResponseData::nested($data, 'restrictions');

        return new self(
            unitGroup: EmbeddedUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
            minGuaranteeType: $minGuaranteeType !== null ? GuaranteeType::fromApi($minGuaranteeType) : null,
            minAdvance: $minAdvance !== [] ? Period::fromArray($minAdvance) : null,
            maxAdvance: $maxAdvance !== [] ? Period::fromArray($maxAdvance) : null,
            available: ResponseData::int($data, 'available'),
            availableUnits: ResponseData::int($data, 'availableUnits'),
            restrictions: $restrictions !== [] ? RateRestrictions::fromArray($restrictions) : null,
            prices: array_map(PerOccupancyPriceItem::fromArray(...), ResponseData::nestedList($data, 'prices')),
        );
    }
}
