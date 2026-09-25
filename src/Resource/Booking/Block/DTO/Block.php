<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Block\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Block\Enum\BlockStatus;
use Oleksyuk\Apaleo\Resource\Booking\Block\Enum\OptionalCutoffBehavior;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\Action;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedGroup;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedRatePlan;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedMarketSegment;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedProperty;
use Oleksyuk\Apaleo\Resource\Shared\DTO\EmbeddedUnitGroup;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Block
{
    /**
     * @param list<BlockTimeSlice> $timeSlices
     * @param list<Action>         $actions
     */
    public function __construct(
        public string $id,
        public EmbeddedGroup $group,
        public BlockStatus $status,
        public EmbeddedProperty $property,
        public EmbeddedRatePlan $ratePlan,
        public EmbeddedUnitGroup $unitGroup,
        public MonetaryValue $grossDailyRate,
        public \DateTimeImmutable $from,
        public \DateTimeImmutable $to,
        public int $pickedReservations,
        public ?EmbeddedMarketSegment $marketSegment,
        public ?string $promoCode,
        public ?string $corporateCode,
        public \DateTimeImmutable $created,
        public \DateTimeImmutable $modified,
        public array $timeSlices,
        public array $actions,
        public ?\DateTimeImmutable $optionalCutoff,
        public ?OptionalCutoffBehavior $optionalCutoffBehavior,
        public ?bool $isOptionalDeductingInventory,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $marketSegment = ResponseData::nested($data, 'marketSegment');
        $optionalCutoffBehavior = ResponseData::nullableString($data, 'optionalCutoffBehavior');

        return new self(
            id: ResponseData::string($data, 'id'),
            group: EmbeddedGroup::fromArray(ResponseData::nested($data, 'group')),
            status: BlockStatus::fromApi(ResponseData::string($data, 'status')),
            property: EmbeddedProperty::fromArray(ResponseData::nested($data, 'property')),
            ratePlan: EmbeddedRatePlan::fromArray(ResponseData::nested($data, 'ratePlan')),
            unitGroup: EmbeddedUnitGroup::fromArray(ResponseData::nested($data, 'unitGroup')),
            grossDailyRate: MonetaryValue::fromArray(ResponseData::nested($data, 'grossDailyRate')),
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            pickedReservations: ResponseData::int($data, 'pickedReservations'),
            marketSegment: $marketSegment !== [] ? EmbeddedMarketSegment::fromArray($marketSegment) : null,
            promoCode: ResponseData::nullableString($data, 'promoCode'),
            corporateCode: ResponseData::nullableString($data, 'corporateCode'),
            created: ResponseData::dateTime($data, 'created'),
            modified: ResponseData::dateTime($data, 'modified'),
            timeSlices: array_map(BlockTimeSlice::fromArray(...), ResponseData::nestedList($data, 'timeSlices')),
            actions: array_map(Action::fromArray(...), ResponseData::nestedList($data, 'actions')),
            optionalCutoff: ResponseData::nullableDateTime($data, 'optionalCutoff'),
            optionalCutoffBehavior: $optionalCutoffBehavior !== null ? OptionalCutoffBehavior::fromApi($optionalCutoffBehavior) : null,
            isOptionalDeductingInventory: \array_key_exists('isOptionalDeductingInventory', $data) ? ResponseData::bool($data, 'isOptionalDeductingInventory') : null,
        );
    }
}
