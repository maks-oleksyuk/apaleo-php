<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Group\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Block\Enum\BlockStatus;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedMarketSegment;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedRatePlan;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedUnitGroup;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

/** The block summary embedded in a {@see Group}; see BlockResource::get() for the full block (time slices, actions). */
final readonly class GroupBlock
{
    public function __construct(
        public string $id,
        public BlockStatus $status,
        public EmbeddedProperty $property,
        public EmbeddedRatePlan $ratePlan,
        public EmbeddedUnitGroup $unitGroup,
        public ?EmbeddedMarketSegment $marketSegment,
        public MonetaryValue $grossDailyRate,
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public int $blockedUnits,
        public int $pickedReservations,
        public \DateTimeImmutable $created,
        public \DateTimeImmutable $modified,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $marketSegment = ResponseData::nested($data, 'marketSegment');

        return new self(
            id: ResponseData::string($data, 'id'),
            status: BlockStatus::fromApi(ResponseData::string($data, 'status')),
            property: EmbeddedProperty::fromArray(ResponseData::nested($data, 'property')),
            ratePlan: EmbeddedRatePlan::fromArray(ResponseData::nested($data, 'ratePlan')),
            unitGroup: EmbeddedUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
            marketSegment: $marketSegment !== [] ? EmbeddedMarketSegment::fromArray($marketSegment) : null,
            grossDailyRate: MonetaryValue::fromArray(ResponseData::nested($data, 'grossDailyRate')),
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            blockedUnits: ResponseData::int($data, 'blockedUnits'),
            pickedReservations: ResponseData::int($data, 'pickedReservations'),
            created: ResponseData::dateTime($data, 'created'),
            modified: ResponseData::dateTime($data, 'modified'),
        );
    }
}
