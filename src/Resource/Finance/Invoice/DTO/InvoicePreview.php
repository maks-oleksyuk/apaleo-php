<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedCompany;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\TaxDetail;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\CreateInvoiceAction;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\BankAccount;
use Oleksyuk\Apaleo\Resource\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Support\ResponseData;

/** What InvoiceResource::create() would produce for the folio, and whether it would work ($createInvoiceAction, $createInvoiceWarning). */
final readonly class InvoicePreview
{
    /**
     * @param list<InvoiceLineItem> $lineItems
     * @param list<InvoicePayment>  $payments
     * @param list<TaxDetail>       $taxDetails
     */
    public function __construct(
        public CreateInvoiceAction $createInvoiceAction,
        public ?CreateInvoiceWarning $createInvoiceWarning,
        public \DateTimeImmutable $invoiceDate,
        public string $folioId,
        public string $propertyId,
        public string $propertyCountryCode,
        public string $languageCode,
        public ?InvoiceRecipient $to,
        public InvoiceSender $from,
        public CommercialInfo $commercialInformation,
        public ?BankAccount $bankAccount,
        public ?string $paymentTerms,
        public array $lineItems,
        public MonetaryValue $subTotal,
        public array $payments,
        public ?MonetaryValue $outstandingPayment,
        public array $taxDetails,
        public MonetaryValue $total,
        public ?StayInfo $stayInfo,
        public ?EmbeddedCompany $company,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $lineItems = ResponseData::nested($data, 'lineItems');

        return new self(
            createInvoiceAction: CreateInvoiceAction::fromApi(ResponseData::string($data, 'createInvoiceAction')),
            createInvoiceWarning: ResponseData::nullableNested($data, 'createInvoiceWarning', CreateInvoiceWarning::fromArray(...)),
            invoiceDate: ResponseData::date($data, 'invoiceDate'),
            folioId: ResponseData::string($data, 'folioId'),
            propertyId: ResponseData::string($data, 'propertyId'),
            propertyCountryCode: ResponseData::string($data, 'propertyCountryCode'),
            languageCode: ResponseData::string($data, 'languageCode'),
            to: ResponseData::nullableNested($data, 'to', InvoiceRecipient::fromArray(...)),
            from: InvoiceSender::fromArray(ResponseData::nested($data, 'from')),
            commercialInformation: CommercialInfo::fromArray(ResponseData::nested($data, 'commercialInformation')),
            bankAccount: ResponseData::nullableNested($data, 'bankAccount', BankAccount::fromArray(...)),
            paymentTerms: ResponseData::nullableString($data, 'paymentTerms'),
            lineItems: array_map(InvoiceLineItem::fromArray(...), ResponseData::nestedList($lineItems, 'lineItems')),
            subTotal: MonetaryValue::fromArray(ResponseData::nested($lineItems, 'subTotal')),
            payments: array_map(InvoicePayment::fromArray(...), ResponseData::nestedList($data, 'payments')),
            outstandingPayment: ResponseData::nullableNested($data, 'outstandingPayment', MonetaryValue::fromArray(...)),
            taxDetails: array_map(TaxDetail::fromArray(...), ResponseData::nestedList($data, 'taxDetails')),
            total: MonetaryValue::fromArray(ResponseData::nested($data, 'total')),
            stayInfo: ResponseData::nullableNested($data, 'stayInfo', StayInfo::fromArray(...)),
            company: ResponseData::nullableNested($data, 'company', EmbeddedCompany::fromArray(...)),
        );
    }
}
