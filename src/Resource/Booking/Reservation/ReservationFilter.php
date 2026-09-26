<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Booking\Reservation;

use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\DateFilter;
use Oleksyuk\Apaleo\Resource\Booking\Reservation\Enum\ReservationStatus;
use Oleksyuk\Apaleo\Resource\Shared\Enum\ChannelCode;
use Oleksyuk\Apaleo\Resource\Shared\Enum\UnitGroupType;
use Oleksyuk\Apaleo\Support\Query;

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
            'propertyIds' => Query::csv($this->propertyIds),
            'ratePlanIds' => Query::csv($this->ratePlanIds),
            'companyIds' => Query::csv($this->companyIds),
            'unitIds' => Query::csv($this->unitIds),
            'unitGroupIds' => Query::csv($this->unitGroupIds),
            'unitGroupTypes' => Query::csv($this->unitGroupTypes),
            'blockIds' => Query::csv($this->blockIds),
            'marketSegmentIds' => Query::csv($this->marketSegmentIds),
            'status' => Query::csv($this->status),
            'dateFilter' => $this->dateFilter?->value,
            'from' => $this->from?->format(\DateTimeInterface::ATOM),
            'to' => $this->to?->format(\DateTimeInterface::ATOM),
            'channelCode' => Query::csv($this->channelCode),
            'sources' => Query::csv($this->sources),
            'validationMessageCategory' => Query::csv($this->validationMessageCategory),
            'externalCode' => $this->externalCode,
            'textSearch' => $this->textSearch,
            'balanceFilter' => Query::csv($this->balanceFilter),
            'allFoliosHaveInvoice' => $this->allFoliosHaveInvoice,
            'isPreCheckedIn' => $this->isPreCheckedIn,
            'hasActivePaymentAccount' => $this->hasActivePaymentAccount,
            'externalReferences' => Query::csv($this->externalReferences),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
