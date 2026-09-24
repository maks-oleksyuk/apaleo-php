<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Folio;

use Oleksyuk\Apaleo\Resource\Finance\Folio\Enum\FolioStatus;
use Oleksyuk\Apaleo\Resource\Finance\Shared\Enum\FolioType;

final readonly class FolioFilter
{
    /**
     * @param list<string> $propertyIds
     * @param list<string> $companyIds
     * @param list<string> $reservationIds
     * @param list<string> $bookingIds
     * @param list<string> $balanceFilter e.g. ["lt_0"], ["gte_100", "lte_200"]
     */
    public function __construct(
        public array $propertyIds = [],
        public array $companyIds = [],
        public array $reservationIds = [],
        public array $bookingIds = [],
        public ?FolioType $type = null,
        public ?FolioStatus $status = null,
        public ?bool $isEmpty = null,
        public ?bool $excludeClosed = null,
        public ?bool $hasInvoices = null,
        public ?bool $onlyMain = null,
        public ?\DateTimeImmutable $createdFrom = null,
        public ?\DateTimeImmutable $createdTo = null,
        public ?\DateTimeImmutable $updatedFrom = null,
        public ?\DateTimeImmutable $updatedTo = null,
        public ?string $externalFolioCode = null,
        public ?string $textSearch = null,
        public array $balanceFilter = [],
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'propertyIds' => implode(',', $this->propertyIds) ?: null,
            'companyIds' => implode(',', $this->companyIds) ?: null,
            'reservationIds' => implode(',', $this->reservationIds) ?: null,
            'bookingIds' => implode(',', $this->bookingIds) ?: null,
            'type' => $this->type?->value,
            'status' => $this->status?->value,
            'isEmpty' => $this->isEmpty,
            'excludeClosed' => $this->excludeClosed,
            'hasInvoices' => $this->hasInvoices,
            'onlyMain' => $this->onlyMain,
            'createdFrom' => $this->createdFrom?->format(\DateTimeInterface::ATOM),
            'createdTo' => $this->createdTo?->format(\DateTimeInterface::ATOM),
            'updatedFrom' => $this->updatedFrom?->format(\DateTimeInterface::ATOM),
            'updatedTo' => $this->updatedTo?->format(\DateTimeInterface::ATOM),
            'externalFolioCode' => $this->externalFolioCode,
            'textSearch' => $this->textSearch,
            'balanceFilter' => implode(',', $this->balanceFilter) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
