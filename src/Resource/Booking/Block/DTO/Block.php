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
        $optionalCutoffBehavior = ResponseData::nullableString($data, 'optionalCutoffBehavior');

        return new self(
            id: ResponseData::string($data, 'id'),
            group: ResponseData::requiredNested($data, 'group', EmbeddedGroup::fromArray(...)),
            status: BlockStatus::fromApi(ResponseData::string($data, 'status')),
            property: ResponseData::requiredNested($data, 'property', EmbeddedProperty::fromArray(...)),
            ratePlan: ResponseData::requiredNested($data, 'ratePlan', EmbeddedRatePlan::fromArray(...)),
            unitGroup: ResponseData::requiredNested($data, 'unitGroup', EmbeddedUnitGroup::fromArray(...)),
            grossDailyRate: ResponseData::requiredNested($data, 'grossDailyRate', MonetaryValue::fromArray(...)),
            from: ResponseData::dateTime($data, 'from'),
            to: ResponseData::dateTime($data, 'to'),
            pickedReservations: ResponseData::int($data, 'pickedReservations'),
            marketSegment: ResponseData::nullableNested($data, 'marketSegment', EmbeddedMarketSegment::fromArray(...)),
            promoCode: ResponseData::nullableString($data, 'promoCode'),
            corporateCode: ResponseData::nullableString($data, 'corporateCode'),
            created: ResponseData::dateTime($data, 'created'),
            modified: ResponseData::dateTime($data, 'modified'),
            timeSlices: ResponseData::mapList($data, 'timeSlices', BlockTimeSlice::fromArray(...)),
            actions: ResponseData::mapList($data, 'actions', Action::fromArray(...)),
            optionalCutoff: ResponseData::nullableDateTime($data, 'optionalCutoff'),
            optionalCutoffBehavior: $optionalCutoffBehavior !== null ? OptionalCutoffBehavior::fromApi($optionalCutoffBehavior) : null,
            isOptionalDeductingInventory: \array_key_exists('isOptionalDeductingInventory', $data) ? ResponseData::bool($data, 'isOptionalDeductingInventory') : null,
        );
    }
}
