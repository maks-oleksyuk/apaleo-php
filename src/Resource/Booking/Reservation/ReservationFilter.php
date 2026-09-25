<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation;

use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\DateFilter;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationStatus;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;

final readonly class ReservationFilter
{
    /**
     * @param list<string>            $propertyIds
     * @param list<string>            $ratePlanIds
     * @param list<string>            $companyIds
     * @param list<string>            $unitIds
     * @param list<string>            $unitGroupIds
     * @param list<UnitGroupType>     $unitGroupTypes
     * @param list<string>            $blockIds
     * @param list<string>            $marketSegmentIds
     * @param list<ReservationStatus> $status
     * @param list<ChannelCode>       $channelCode
     * @param list<string>            $sources
     * @param list<string>            $validationMessageCategory
     * @param list<string>            $balanceFilter
     * @param list<string>            $externalReferences
     */
    public function __construct(
        public ?string $bookingId = null,
        public array $propertyIds = [],
        public array $ratePlanIds = [],
        public array $companyIds = [],
        public array $unitIds = [],
        public array $unitGroupIds = [],
        public array $unitGroupTypes = [],
        public array $blockIds = [],
        public array $marketSegmentIds = [],
        public array $status = [],
        public ?DateFilter $dateFilter = null,
        public ?\DateTimeImmutable $from = null,
        public ?\DateTimeImmutable $to = null,
        public array $channelCode = [],
        public array $sources = [],
        public array $validationMessageCategory = [],
        public ?string $externalCode = null,
        public ?string $textSearch = null,
        public array $balanceFilter = [],
        public ?bool $allFoliosHaveInvoice = null,
        public ?bool $isPreCheckedIn = null,
        public ?bool $hasActivePaymentAccount = null,
        public array $externalReferences = [],
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'bookingId' => $this->bookingId,
            'propertyIds' => implode(',', $this->propertyIds) ?: null,
            'ratePlanIds' => implode(',', $this->ratePlanIds) ?: null,
            'companyIds' => implode(',', $this->companyIds) ?: null,
            'unitIds' => implode(',', $this->unitIds) ?: null,
            'unitGroupIds' => implode(',', $this->unitGroupIds) ?: null,
            'unitGroupTypes' => implode(',', array_map(static fn (UnitGroupType $t): string => $t->value, $this->unitGroupTypes)) ?: null,
            'blockIds' => implode(',', $this->blockIds) ?: null,
            'marketSegmentIds' => implode(',', $this->marketSegmentIds) ?: null,
            'status' => implode(',', array_map(static fn (ReservationStatus $s): string => $s->value, $this->status)) ?: null,
            'dateFilter' => $this->dateFilter?->value,
            'from' => $this->from?->format(\DateTimeInterface::ATOM),
            'to' => $this->to?->format(\DateTimeInterface::ATOM),
            'channelCode' => implode(',', array_map(static fn (ChannelCode $c): string => $c->value, $this->channelCode)) ?: null,
            'sources' => implode(',', $this->sources) ?: null,
            'validationMessageCategory' => implode(',', $this->validationMessageCategory) ?: null,
            'externalCode' => $this->externalCode,
            'textSearch' => $this->textSearch,
            'balanceFilter' => implode(',', $this->balanceFilter) ?: null,
            'allFoliosHaveInvoice' => $this->allFoliosHaveInvoice,
            'isPreCheckedIn' => $this->isPreCheckedIn,
            'hasActivePaymentAccount' => $this->hasActivePaymentAccount,
            'externalReferences' => implode(',', $this->externalReferences) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
