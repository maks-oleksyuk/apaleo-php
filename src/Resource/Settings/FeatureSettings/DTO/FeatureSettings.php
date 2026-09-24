<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Settings\FeatureSettings\DTO;

use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class FeatureSettings
{
    /** @param ?float $maxAmountForMinimalInvoices below this amount an invoice may carry only the guest's name */
    public function __construct(
        public bool $areCustomRevenueSubAccountsEnabled,
        public bool $performAccountingForOpenInvoiceActions,
        public bool $showRecipientForEachLineItemOnTheInvoice,
        public ?float $maxAmountForMinimalInvoices,
        public string $invoiceNumberPattern,
        public string $advanceInvoiceNumberPattern,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            areCustomRevenueSubAccountsEnabled: ResponseData::bool($data, 'areCustomRevenueSubAccountsEnabled'),
            performAccountingForOpenInvoiceActions: ResponseData::bool($data, 'performAccountingForOpenInvoiceActions'),
            showRecipientForEachLineItemOnTheInvoice: ResponseData::bool($data, 'showRecipientForEachLineItemOnTheInvoice'),
            maxAmountForMinimalInvoices: ResponseData::nullableFloat($data, 'maxAmountForMinimalInvoices'),
            invoiceNumberPattern: ResponseData::string($data, 'invoiceNumberPattern'),
            advanceInvoiceNumberPattern: ResponseData::string($data, 'advanceInvoiceNumberPattern'),
        );
    }
}
