<?php

declare(strict_types=1);

namespace Oleksyuk\Apaleo\Resource\Finance\Invoice\DTO;

use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\EmbeddedCompany;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\MonetaryValue;
use Oleksyuk\Apaleo\Resource\Booking\Shared\DTO\TaxDetail;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceAction;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceCancellationReason;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceStatus;
use Oleksyuk\Apaleo\Resource\Finance\Invoice\Enum\InvoiceType;
use Oleksyuk\Apaleo\Resource\Inventory\Property\DTO\BankAccount;
use Oleksyuk\Apaleo\Support\ResponseData;

final readonly class Invoice
{
    /**
     * @param list<InvoiceAction>   $allowedActions
     * @param list<InvoiceLineItem> $lineItems
     * @param list<InvoicePayment>  $payments
     * @param list<TaxDetail>       $taxDetails
     */
    public function __construct(
        public string $id,
        public string $number,
        public InvoiceType $type,
        public ?string $series,
        public InvoiceStatus $status,
        public bool $paymentSettled,
        public \DateTimeImmutable $created,
        public \DateTimeImmutable $invoiceDate,
        public string $folioId,
        public string $propertyId,
        public string $propertyCountryCode,
        public string $languageCode,
        public InvoiceRecipient $to,
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
        public ?string $relatedInvoiceNumber,
        public ?string $writeOffReason,
        public ?InvoiceCancellationReason $cancellationReasonCode,
        public array $allowedActions,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $bankAccount = ResponseData::nested($data, 'bankAccount');
        $outstandingPayment = ResponseData::nested($data, 'outstandingPayment');
        $company = ResponseData::nested($data, 'company');
        $cancellationReason = ResponseData::nullableString($data, 'cancellationReasonCode');
        $lineItems = ResponseData::nested($data, 'lineItems');

        return new self(
            id: ResponseData::string($data, 'id'),
            number: ResponseData::string($data, 'number'),
            type: InvoiceType::fromApi(ResponseData::string($data, 'type')),
            series: ResponseData::nullableString($data, 'series'),
            status: InvoiceStatus::fromApi(ResponseData::string($data, 'status')),
            paymentSettled: ResponseData::bool($data, 'paymentSettled'),
            created: ResponseData::dateTime($data, 'created'),
            invoiceDate: ResponseData::date($data, 'invoiceDate'),
            folioId: ResponseData::string($data, 'folioId'),
            propertyId: ResponseData::string($data, 'propertyId'),
            propertyCountryCode: ResponseData::string($data, 'propertyCountryCode'),
            languageCode: ResponseData::string($data, 'languageCode'),
            to: InvoiceRecipient::fromArray(ResponseData::nested($data, 'to')),
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
            relatedInvoiceNumber: ResponseData::nullableString($data, 'relatedInvoiceNumber'),
            writeOffReason: ResponseData::nullableString($data, 'writeOffReason'),
            cancellationReasonCode: $cancellationReason !== null ? InvoiceCancellationReason::fromApi($cancellationReason) : null,
            allowedActions: array_map(InvoiceAction::fromApi(...), ResponseData::stringListOrEmpty($data, 'allowedActions')),
        );
    }
}
