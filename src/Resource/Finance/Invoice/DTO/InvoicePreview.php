<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedCompany;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\TaxDetail;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\CreateInvoiceAction;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\BankAccount;
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
        $to = ResponseData::nested($data, 'to');
        $bankAccount = ResponseData::nested($data, 'bankAccount');
        $outstandingPayment = ResponseData::nested($data, 'outstandingPayment');
        $company = ResponseData::nested($data, 'company');
        $lineItems = ResponseData::nested($data, 'lineItems');

        return new self(
            createInvoiceAction: CreateInvoiceAction::fromApi(ResponseData::string($data, 'createInvoiceAction')),
            createInvoiceWarning: CreateInvoiceWarning::fromNested($data, 'createInvoiceWarning'),
            invoiceDate: ResponseData::date($data, 'invoiceDate'),
            folioId: ResponseData::string($data, 'folioId'),
            propertyId: ResponseData::string($data, 'propertyId'),
            propertyCountryCode: ResponseData::string($data, 'propertyCountryCode'),
            languageCode: ResponseData::string($data, 'languageCode'),
            to: $to !== [] ? InvoiceRecipient::fromArray($to) : null,
            from: InvoiceSender::fromArray(ResponseData::nested($data, 'from')),
            commercialInformation: CommercialInfo::fromArray(ResponseData::nested($data, 'commercialInformation')),
            bankAccount: $bankAccount !== [] ? BankAccount::fromArray($bankAccount) : null,
            paymentTerms: ResponseData::nullableString($data, 'paymentTerms'),
            lineItems: array_map(InvoiceLineItem::fromArray(...), ResponseData::nestedList($lineItems, 'lineItems')),
            subTotal: MonetaryValue::fromArray(ResponseData::nested($lineItems, 'subTotal')),
            payments: array_map(InvoicePayment::fromArray(...), ResponseData::nestedList($data, 'payments')),
            outstandingPayment: $outstandingPayment !== [] ? MonetaryValue::fromArray($outstandingPayment) : null,
            taxDetails: array_map(TaxDetail::fromArray(...), ResponseData::nestedList($data, 'taxDetails')),
            total: MonetaryValue::fromArray(ResponseData::nested($data, 'total')),
            stayInfo: StayInfo::fromNested($data, 'stayInfo'),
            company: $company !== [] ? EmbeddedCompany::fromArray($company) : null,
        );
    }
}
