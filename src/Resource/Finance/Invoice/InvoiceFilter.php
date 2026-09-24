<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice;

use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceRecipientType;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceStatus;

final readonly class InvoiceFilter
{
    /**
     * @param list<string> $propertyIds
     * @param list<string> $reservationIds
     * @param list<string> $bookingIds
     * @param list<string> $folioIds
     * @param list<string> $companyIds
     * @param list<string> $dateFilter               on the invoice date, e.g. ["gte_2026-01-01", "lt_2026-02-01"]
     * @param list<string> $outstandingPaymentFilter e.g. ["gt_0"]
     */
    public function __construct(
        public ?string $number = null,
        public ?InvoiceStatus $status = null,
        public ?bool $paymentSettled = null,
        public ?bool $checkedOutOnAccountsReceivable = null,
        public ?InvoiceRecipientType $recipientType = null,
        public ?string $nameSearch = null,
        public array $propertyIds = [],
        public array $reservationIds = [],
        public array $bookingIds = [],
        public array $folioIds = [],
        public array $companyIds = [],
        public array $dateFilter = [],
        public array $outstandingPaymentFilter = [],
    ) {}

    /** @return array<string, mixed> */
    public function toQuery(): array
    {
        return array_filter([
            'number' => $this->number,
            'status' => $this->status?->value,
            'paymentSettled' => $this->paymentSettled,
            'checkedOutOnAccountsReceivable' => $this->checkedOutOnAccountsReceivable,
            'recipientType' => $this->recipientType?->value,
            'nameSearch' => $this->nameSearch,
            'propertyIds' => implode(',', $this->propertyIds) ?: null,
            'reservationIds' => implode(',', $this->reservationIds) ?: null,
            'bookingIds' => implode(',', $this->bookingIds) ?: null,
            'folioIds' => implode(',', $this->folioIds) ?: null,
            'companyIds' => implode(',', $this->companyIds) ?: null,
            'dateFilter' => implode(',', $this->dateFilter) ?: null,
            'outstandingPaymentFilter' => implode(',', $this->outstandingPaymentFilter) ?: null,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
